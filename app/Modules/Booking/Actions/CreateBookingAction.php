<?php

namespace App\Modules\Booking\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use App\Modules\Booking\DTOs\CreateBookingData;
use App\Modules\Booking\Exceptions\BookingDateUnavailableException;
use App\Modules\Booking\Services\BookingAvailabilityService;
use App\Modules\Booking\Services\BookingPricingService;
use App\Modules\Booking\Services\BusinessDayCalculator;
use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CreateBookingAction
{
    public function __construct(
        private readonly BookingAvailabilityService $availabilityService,
        private readonly BookingPricingService $pricingService,
        private readonly BusinessDayCalculator $businessDayCalculator,
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function execute(CreateBookingData $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            if (! $this->availabilityService->isDateAvailable($data->eventDate)) {
                throw new BookingDateUnavailableException();
            }

            $user = User::query()->findOrFail($data->userId);

            if ((string) $user->tenant_id !== (string) $this->tenantContext->id()) {
                throw new AuthorizationException('Selected user does not belong to the current tenant.');
            }

            try {
                return Booking::query()->create([
                    'user_id' => $user->id,
                    'event_date' => $data->eventDate->toDateString(),
                    'period_type' => $data->periodType,
                    'total_price' => $this->pricingService->priceFor($data->periodType),
                    'status' => BookingStatus::PENDING,
                    'payment_expires_at' => $this->businessDayCalculator->addBusinessDays(now(), 2),
                ]);
            } catch (QueryException $exception) {
                if ($this->isUniqueConstraintViolation($exception)) {
                    throw new BookingDateUnavailableException();
                }

                throw $exception;
            }
        });
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;
        $driverCode = (string) ($exception->errorInfo[1] ?? '');
        $message = $exception->getMessage();

        return in_array($sqlState, ['23000', '23505'], true)
            || str_contains($driverCode, '19')
            || str_contains(strtolower($message), 'unique');
    }
}