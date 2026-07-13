<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\TransactionStatus;
use App\Models\Booking;
use App\Models\Tenant;
use App\Models\TenantConfig;
use App\Models\Transaction;
use App\Models\User;
use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelExpiredBookingsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancelamento_por_expiracao(): void
    {
        [, $booking, $transaction] = $this->expiredBookingContext();

        $this->artisan('bookings:cancel-expired')
            ->assertSuccessful();

        $this->assertSame(BookingStatus::CANCELED, $booking->fresh()->status);
        $this->assertSame(TransactionStatus::EXPIRED, $transaction->fresh()->status);
    }

    public function test_processa_varios_tenants_com_seguranca(): void
    {
        [, $bookingA, $transactionA] = $this->expiredBookingContext('cliente-a');
        [, $bookingB, $transactionB] = $this->expiredBookingContext('cliente-b');

        $this->artisan('bookings:cancel-expired')
            ->assertSuccessful();

        $this->assertSame(BookingStatus::CANCELED, $bookingA->fresh()->status);
        $this->assertSame(TransactionStatus::EXPIRED, $transactionA->fresh()->status);
        $this->assertSame(BookingStatus::CANCELED, $bookingB->fresh()->status);
        $this->assertSame(TransactionStatus::EXPIRED, $transactionB->fresh()->status);
    }

    public function test_execucao_repetida_e_idempotente(): void
    {
        [, $booking, $transaction] = $this->expiredBookingContext();

        $this->artisan('bookings:cancel-expired')
            ->assertSuccessful();

        $firstUpdatedAt = $transaction->fresh()->updated_at;

        $this->artisan('bookings:cancel-expired')
            ->assertSuccessful();

        $this->assertSame(BookingStatus::CANCELED, $booking->fresh()->status);
        $this->assertSame(TransactionStatus::EXPIRED, $transaction->fresh()->status);
        $this->assertTrue($transaction->fresh()->updated_at?->equalTo($firstUpdatedAt));
    }

    private function expiredBookingContext(?string $subdomain = null): array
    {
        $tenant = Tenant::factory()->create([
            'subdomain' => $subdomain ?? 'cliente-'.fake()->unique()->numerify('###'),
        ]);

        return $this->runInTenant($tenant, function () use ($tenant) {
            TenantConfig::query()->create();
            $user = User::factory()->create();
            $booking = Booking::query()->create([
                'user_id' => $user->id,
                'event_date' => '2026-08-01',
                'period_type' => 'regular',
                'total_price' => 300,
                'status' => BookingStatus::PENDING,
                'payment_expires_at' => now()->subHour(),
            ]);
            $transaction = Transaction::query()->create([
                'booking_id' => $booking->id,
                'gateway_name' => 'abacatepay',
                'gateway_transaction_id' => 'pending-'.$booking->id,
                'payment_method' => 'pix',
                'status' => TransactionStatus::PENDING,
                'amount' => 300,
                'expires_at' => now()->subHour(),
            ]);

            return [$tenant, $booking, $transaction];
        });
    }

    private function runInTenant(Tenant $tenant, callable $callback): mixed
    {
        return app(TenantContext::class)->run($tenant, $callback(...));
    }
}