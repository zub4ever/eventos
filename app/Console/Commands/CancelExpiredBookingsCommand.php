<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Enums\TransactionStatus;
use App\Models\Booking;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Modules\Booking\Events\BookingCanceled;
use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelExpiredBookingsCommand extends Command
{
    protected $signature = 'bookings:cancel-expired';

    protected $description = 'Cancel pending bookings whose payment expiration has passed.';

    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $processed = 0;
        $canceled = 0;
        $expiredTransactions = 0;

        Booking::withoutGlobalScopes()
            ->where('status', BookingStatus::PENDING)
            ->whereNotNull('payment_expires_at')
            ->where('payment_expires_at', '<=', now())
            ->orderBy('payment_expires_at')
            ->chunk(100, function ($bookings) use (&$processed, &$canceled, &$expiredTransactions): void {
                foreach ($bookings as $booking) {
                    $bookingId = (string) $booking->id;
                    $tenantId = (string) $booking->tenant_id;

                    $processed++;

                    $tenant = Tenant::query()->find($tenantId);

                    if ($tenant === null) {
                        continue;
                    }

                    $result = $this->tenantContext->run($tenant, function () use ($bookingId): array {
                        return DB::transaction(function () use ($bookingId): array {
                            /** @var Booking|null $scopedBooking */
                            $scopedBooking = Booking::query()->find($bookingId);

                            if ($scopedBooking === null || $scopedBooking->status !== BookingStatus::PENDING) {
                                return ['canceled' => false, 'expired_transactions' => 0];
                            }

                            $scopedBooking->forceFill([
                                'status' => BookingStatus::CANCELED,
                            ])->save();

                            $updatedTransactions = Transaction::query()
                                ->where('booking_id', $scopedBooking->id)
                                ->where('status', TransactionStatus::PENDING)
                                ->update([
                                    'status' => TransactionStatus::EXPIRED,
                                    'updated_at' => now(),
                                ]);

                            event(new BookingCanceled($scopedBooking, 'payment_expired'));

                            return ['canceled' => true, 'expired_transactions' => $updatedTransactions];
                        });
                    });

                    if ($result['canceled']) {
                        $canceled++;
                        $expiredTransactions += $result['expired_transactions'];
                    }
                }
            });

        Log::info('Expired bookings cancellation summary', [
            'processed' => $processed,
            'canceled' => $canceled,
            'expired_transactions' => $expiredTransactions,
        ]);

        $this->info("Processed {$processed} booking(s), canceled {$canceled}, expired {$expiredTransactions} transaction(s).");

        return self::SUCCESS;
    }
}