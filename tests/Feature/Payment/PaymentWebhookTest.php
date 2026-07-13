<?php

namespace Tests\Feature\Payment;

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

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('tenancy.domain', 'saas.com.br');
        config()->set('tenancy.central_domains', ['saas.com.br', 'www.saas.com.br', 'api.saas.com.br']);
        config()->set('services.abacatepay.webhook_secret', 'secret-abacate');
        config()->set('services.asaas.webhook_secret', 'secret-asaas');
    }

    public function test_webhook_valido_abacatepay(): void
    {
        [, $booking, $transaction] = $this->bookingWithTransaction('abacatepay');

        $response = $this->withServerVariables(['HTTP_HOST' => 'saas.com.br'])
            ->withHeaders(['X-AbacatePay-Token' => 'secret-abacate'])
            ->postJson('/api/webhooks/payments/abacatepay', [
                'id' => $transaction->gateway_transaction_id,
                'status' => 'paid',
                'paid_at' => '2026-07-15T10:00:00-03:00',
                'event' => 'charge.paid',
            ]);

        $response->assertOk();
        $this->assertSame(TransactionStatus::PAID, $transaction->fresh()->status);
        $this->assertSame(BookingStatus::CONFIRMED, $booking->fresh()->status);
    }

    public function test_webhook_com_assinatura_invalida(): void
    {
        [, , $transaction] = $this->bookingWithTransaction('abacatepay');

        $this->withServerVariables(['HTTP_HOST' => 'saas.com.br'])
            ->withHeaders(['X-AbacatePay-Token' => 'errado'])
            ->postJson('/api/webhooks/payments/abacatepay', [
                'id' => $transaction->gateway_transaction_id,
                'status' => 'paid',
                'event' => 'charge.paid',
            ])
            ->assertForbidden();
    }

    public function test_webhook_duplicado_e_idempotente(): void
    {
        [, $booking, $transaction] = $this->bookingWithTransaction('abacatepay');

        $payload = [
            'id' => $transaction->gateway_transaction_id,
            'status' => 'paid',
            'paid_at' => '2026-07-15T10:00:00-03:00',
            'event' => 'charge.paid',
        ];

        $headers = ['X-AbacatePay-Token' => 'secret-abacate'];

        $this->withServerVariables(['HTTP_HOST' => 'saas.com.br'])->withHeaders($headers)
            ->postJson('/api/webhooks/payments/abacatepay', $payload)
            ->assertOk();

        $firstPaidAt = $transaction->fresh()->paid_at;

        $this->withServerVariables(['HTTP_HOST' => 'saas.com.br'])->withHeaders($headers)
            ->postJson('/api/webhooks/payments/abacatepay', $payload)
            ->assertOk();

        $this->assertTrue($transaction->fresh()->paid_at?->equalTo($firstPaidAt));
        $this->assertSame(BookingStatus::CONFIRMED, $booking->fresh()->status);
    }

    public function test_transacao_inexistente_retorna_not_found(): void
    {
        $this->withServerVariables(['HTTP_HOST' => 'saas.com.br'])
            ->withHeaders(['X-AbacatePay-Token' => 'secret-abacate'])
            ->postJson('/api/webhooks/payments/abacatepay', [
                'id' => 'inexistente',
                'status' => 'paid',
                'event' => 'charge.paid',
            ])
            ->assertNotFound();
    }

    public function test_pagamento_aprovado_asaas_confirma_reserva(): void
    {
        [, $booking, $transaction] = $this->bookingWithTransaction('asaas');

        $this->withServerVariables(['HTTP_HOST' => 'saas.com.br'])
            ->withHeaders(['asaas-access-token' => 'secret-asaas'])
            ->postJson('/api/webhooks/payments/asaas', [
                'event' => 'PAYMENT_RECEIVED',
                'payment' => [
                    'id' => $transaction->gateway_transaction_id,
                    'status' => 'RECEIVED',
                    'paymentDate' => '2026-07-15 10:00:00',
                ],
            ])
            ->assertOk();

        $this->assertSame(TransactionStatus::PAID, $transaction->fresh()->status);
        $this->assertSame(BookingStatus::CONFIRMED, $booking->fresh()->status);
    }

    public function test_pagamento_recusado_atualiza_transacao_sem_confirmar_reserva(): void
    {
        [, $booking, $transaction] = $this->bookingWithTransaction('abacatepay');

        $this->withServerVariables(['HTTP_HOST' => 'saas.com.br'])
            ->withHeaders(['X-AbacatePay-Token' => 'secret-abacate'])
            ->postJson('/api/webhooks/payments/abacatepay', [
                'id' => $transaction->gateway_transaction_id,
                'status' => 'failed',
                'event' => 'charge.failed',
            ])
            ->assertOk();

        $this->assertSame(TransactionStatus::FAILED, $transaction->fresh()->status);
        $this->assertSame(BookingStatus::PENDING, $booking->fresh()->status);
    }

    public function test_reserva_ja_cancelada_nao_e_reconfirmada(): void
    {
        [, $booking, $transaction] = $this->bookingWithTransaction('abacatepay', BookingStatus::CANCELED);

        $this->withServerVariables(['HTTP_HOST' => 'saas.com.br'])
            ->withHeaders(['X-AbacatePay-Token' => 'secret-abacate'])
            ->postJson('/api/webhooks/payments/abacatepay', [
                'id' => $transaction->gateway_transaction_id,
                'status' => 'paid',
                'paid_at' => '2026-07-15T10:00:00-03:00',
                'event' => 'charge.paid',
            ])
            ->assertOk();

        $this->assertSame(BookingStatus::CANCELED, $booking->fresh()->status);
        $this->assertSame(TransactionStatus::PAID, $transaction->fresh()->status);
    }

    private function bookingWithTransaction(string $provider, BookingStatus $bookingStatus = BookingStatus::PENDING): array
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'cliente-'.fake()->unique()->numerify('###')]);

        return $this->runInTenant($tenant, function () use ($provider, $bookingStatus, $tenant) {
            TenantConfig::query()->create();
            $user = User::factory()->create();
            $booking = Booking::query()->create([
                'user_id' => $user->id,
                'event_date' => '2026-07-31',
                'period_type' => 'regular',
                'total_price' => 300,
                'status' => $bookingStatus,
                'payment_expires_at' => now()->addDays(2),
            ]);
            $transaction = Transaction::query()->create([
                'booking_id' => $booking->id,
                'gateway_name' => $provider,
                'gateway_transaction_id' => $provider.'-tx-1',
                'payment_method' => 'pix',
                'status' => TransactionStatus::PENDING,
                'amount' => 300,
                'expires_at' => now()->addDays(2),
            ]);

            return [$tenant, $booking, $transaction];
        });
    }

    private function runInTenant(Tenant $tenant, callable $callback): mixed
    {
        return app(TenantContext::class)->run($tenant, $callback(...));
    }
}