<?php

namespace Tests\Feature\Payment;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Tenant;
use App\Models\TenantConfig;
use App\Models\User;
use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookingPaymentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('tenancy.domain', 'saas.com.br');
        config()->set('tenancy.central_domains', ['saas.com.br', 'www.saas.com.br', 'api.saas.com.br']);
    }

    public function test_pix_abacatepay(): void
    {
        config()->set('services.payment_provider', 'abacatepay');
        config()->set('services.abacatepay.base_url', 'https://abacatepay.test');

        Http::fake([
            'https://abacatepay.test/*' => Http::response([
                'id' => 'abtx_1',
                'status' => 'pending',
                'pix' => [
                    'qr_code' => 'qr-123',
                    'copy_paste' => 'pix-copy',
                ],
                'expires_at' => '2026-07-16T10:00:00-03:00',
            ], 200),
        ]);

        [$tenant, $user, $booking] = $this->bookableContext();

        $response = $this->actingAs($user)
            ->withServerVariables(['HTTP_HOST' => $tenant->subdomain.'.saas.com.br'])
            ->postJson('/api/bookings/'.$booking->id.'/payments', [
                'payment_method' => 'pix',
            ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'gateway_name' => 'abacatepay',
            'payment_method' => 'pix',
            'pix_qr_code' => 'qr-123',
            'pix_copy_paste' => 'pix-copy',
        ]);
    }

    public function test_pix_asaas(): void
    {
        config()->set('services.payment_provider', 'asaas');
        config()->set('services.asaas.base_url', 'https://asaas.test');

        Http::fake([
            'https://asaas.test/*' => Http::response([
                'id' => 'asaas_1',
                'status' => 'PENDING',
                'pix' => [
                    'qrCode' => 'asaas-qr',
                    'payload' => 'asaas-copy',
                ],
                'dueDate' => '2026-07-16',
            ], 200),
        ]);

        [$tenant, $user, $booking] = $this->bookableContext();

        $this->actingAs($user)
            ->withServerVariables(['HTTP_HOST' => $tenant->subdomain.'.saas.com.br'])
            ->postJson('/api/bookings/'.$booking->id.'/payments', [
                'payment_method' => 'pix',
            ])
            ->assertCreated()
            ->assertJsonFragment([
                'gateway_name' => 'asaas',
                'pix_qr_code' => 'asaas-qr',
                'pix_copy_paste' => 'asaas-copy',
            ]);
    }

    public function test_boleto_asaas(): void
    {
        config()->set('services.payment_provider', 'asaas');
        config()->set('services.asaas.base_url', 'https://asaas.test');

        Http::fake([
            'https://asaas.test/*' => Http::response([
                'id' => 'asaas_boleto',
                'status' => 'PENDING',
                'bankSlipUrl' => 'https://boleto.test/123',
                'dueDate' => '2026-07-16',
            ], 200),
        ]);

        [$tenant, $user, $booking] = $this->bookableContext();

        $this->actingAs($user)
            ->withServerVariables(['HTTP_HOST' => $tenant->subdomain.'.saas.com.br'])
            ->postJson('/api/bookings/'.$booking->id.'/payments', [
                'payment_method' => 'boleto',
            ])
            ->assertCreated()
            ->assertJsonFragment([
                'gateway_name' => 'asaas',
                'payment_method' => 'boleto',
                'boleto_url' => 'https://boleto.test/123',
            ]);
    }

    public function test_cartao_asaas(): void
    {
        config()->set('services.payment_provider', 'asaas');
        config()->set('services.asaas.base_url', 'https://asaas.test');

        Http::fake([
            'https://asaas.test/*' => Http::response([
                'id' => 'asaas_card',
                'status' => 'PENDING',
                'dueDate' => '2026-07-16',
            ], 200),
        ]);

        [$tenant, $user, $booking] = $this->bookableContext();

        $this->actingAs($user)
            ->withServerVariables(['HTTP_HOST' => $tenant->subdomain.'.saas.com.br'])
            ->postJson('/api/bookings/'.$booking->id.'/payments', [
                'payment_method' => 'credit_card',
            ])
            ->assertCreated()
            ->assertJsonFragment([
                'gateway_name' => 'asaas',
                'payment_method' => 'credit_card',
            ]);
    }

    public function test_gateway_invalido(): void
    {
        config()->set('services.payment_provider', 'desconhecido');

        [$tenant, $user, $booking] = $this->bookableContext();

        $this->actingAs($user)
            ->withServerVariables(['HTTP_HOST' => $tenant->subdomain.'.saas.com.br'])
            ->postJson('/api/bookings/'.$booking->id.'/payments', [
                'payment_method' => 'pix',
            ])
            ->assertStatus(422);
    }

    public function test_erro_externo_retorna_bad_gateway(): void
    {
        config()->set('services.payment_provider', 'abacatepay');
        config()->set('services.abacatepay.base_url', 'https://abacatepay.test');

        Http::fake([
            'https://abacatepay.test/*' => Http::response(['error' => 'upstream'], 500),
        ]);

        [$tenant, $user, $booking] = $this->bookableContext();

        $this->actingAs($user)
            ->withServerVariables(['HTTP_HOST' => $tenant->subdomain.'.saas.com.br'])
            ->postJson('/api/bookings/'.$booking->id.'/payments', [
                'payment_method' => 'pix',
            ])
            ->assertStatus(502);
    }

    public function test_reserva_expirada(): void
    {
        [$tenant, $user, $booking] = $this->bookableContext(expired: true);

        $this->actingAs($user)
            ->withServerVariables(['HTTP_HOST' => $tenant->subdomain.'.saas.com.br'])
            ->postJson('/api/bookings/'.$booking->id.'/payments', [
                'payment_method' => 'pix',
            ])
            ->assertStatus(422);
    }

    public function test_reserva_ja_confirmada(): void
    {
        [$tenant, $user, $booking] = $this->bookableContext(status: BookingStatus::CONFIRMED);

        $this->actingAs($user)
            ->withServerVariables(['HTTP_HOST' => $tenant->subdomain.'.saas.com.br'])
            ->postJson('/api/bookings/'.$booking->id.'/payments', [
                'payment_method' => 'pix',
            ])
            ->assertStatus(422);
    }

    public function test_isolamento_entre_tenants_no_pagamento(): void
    {
        [$tenantA, $userA] = $this->tenantWithUser('cliente-a');
        [, , $bookingB] = $this->bookableContext(subdomain: 'cliente-b');

        $this->actingAs($userA)
            ->withServerVariables(['HTTP_HOST' => $tenantA->subdomain.'.saas.com.br'])
            ->postJson('/api/bookings/'.$bookingB->id.'/payments', [
                'payment_method' => 'pix',
            ])
            ->assertNotFound();
    }

    private function bookableContext(
        ?string $subdomain = null,
        BookingStatus $status = BookingStatus::PENDING,
        bool $expired = false,
    ): array {
        [$tenant, $user] = $this->tenantWithUser($subdomain);

        $booking = $this->runInTenant($tenant, function () use ($user, $status, $expired) {
            TenantConfig::query()->create();

            return Booking::query()->create([
                'user_id' => $user->id,
                'event_date' => '2026-07-30',
                'period_type' => 'regular',
                'total_price' => 300,
                'status' => $status,
                'payment_expires_at' => $expired ? now()->subMinute() : now()->addDays(2),
            ]);
        });

        return [$tenant, $user, $booking];
    }

    private function tenantWithUser(?string $subdomain = null): array
    {
        $tenant = Tenant::factory()->create([
            'subdomain' => $subdomain ?? 'cliente-'.fake()->unique()->numerify('###'),
        ]);

        $user = $this->runInTenant($tenant, fn () => User::factory()->create([
            'role' => UserRole::CLIENT,
        ]));

        return [$tenant, $user];
    }

    private function runInTenant(Tenant $tenant, callable $callback): mixed
    {
        return app(TenantContext::class)->run($tenant, $callback(...));
    }
}