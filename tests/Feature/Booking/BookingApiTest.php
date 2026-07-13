<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\CalendarBlock;
use App\Models\Tenant;
use App\Models\TenantConfig;
use App\Models\User;
use App\Modules\Booking\Services\BookingAvailabilityService;
use App\Modules\Tenancy\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('tenancy.domain', 'saas.com.br');
        config()->set('tenancy.central_domains', ['saas.com.br', 'www.saas.com.br', 'api.saas.com.br']);
    }

    public function test_data_ocupada_retorna_conflito(): void
    {
        [$tenant, $user] = $this->tenantWithUser();

        $this->runInTenant($tenant, function () use ($user): void {
            TenantConfig::query()->create();
            Booking::query()->create([
                'user_id' => $user->id,
                'event_date' => '2026-07-15',
                'period_type' => 'regular',
                'total_price' => 300,
                'status' => BookingStatus::PENDING,
                'payment_expires_at' => now()->addDays(2),
            ]);
        });

        $response = $this->actingAs($user)
            ->withServerVariables(['HTTP_HOST' => $tenant->subdomain.'.saas.com.br'])
            ->postJson('/api/bookings', [
                'event_date' => '2026-07-15',
                'period_type' => 'regular',
            ]);

        $response->assertStatus(409);
    }

    public function test_bloqueio_manual_marca_data_indisponivel(): void
    {
        [$tenant, $user] = $this->tenantWithUser();

        $this->runInTenant($tenant, function () use ($user): void {
            CalendarBlock::query()->create([
                'blocked_date' => '2026-07-20',
                'created_by' => $user->id,
            ]);
        });

        $response = $this->withServerVariables(['HTTP_HOST' => $tenant->subdomain.'.saas.com.br'])
            ->getJson('/api/public/calendar?month=2026-07');

        $response->assertOk();
        $response->assertJsonFragment([
            'date' => '2026-07-20',
            'available' => false,
        ]);
    }

    public function test_concorrencia_retorna_conflito_quando_constraint_unica_e_violada(): void
    {
        [$tenant, $user] = $this->tenantWithUser();

        $this->runInTenant($tenant, function () use ($user): void {
            TenantConfig::query()->create();
            Booking::query()->create([
                'user_id' => $user->id,
                'event_date' => '2026-07-25',
                'period_type' => 'regular',
                'total_price' => 300,
                'status' => BookingStatus::PENDING,
                'payment_expires_at' => now()->addDays(2),
            ]);
        });

        $mock = Mockery::mock(BookingAvailabilityService::class);
        $mock->shouldReceive('isDateAvailable')->andReturnTrue();
        $mock->shouldReceive('monthlyAvailability')->andReturn(collect());
        $this->instance(BookingAvailabilityService::class, $mock);

        $response = $this->actingAs($user)
            ->withServerVariables(['HTTP_HOST' => $tenant->subdomain.'.saas.com.br'])
            ->postJson('/api/bookings', [
                'event_date' => '2026-07-25',
                'period_type' => 'regular',
            ]);

        $response->assertStatus(409);
    }

    public function test_isolamento_entre_tenants_no_acesso_da_reserva(): void
    {
        [$tenantA, $userA] = $this->tenantWithUser('cliente-a');
        [$tenantB, $userB] = $this->tenantWithUser('cliente-b');

        $bookingB = $this->runInTenant($tenantB, function () use ($userB) {
            TenantConfig::query()->create();

            return Booking::query()->create([
                'user_id' => $userB->id,
                'event_date' => '2026-07-28',
                'period_type' => 'regular',
                'total_price' => 300,
                'status' => BookingStatus::PENDING,
                'payment_expires_at' => now()->addDays(2),
            ]);
        });

        $this->actingAs($userA)
            ->withServerVariables(['HTTP_HOST' => $tenantA->subdomain.'.saas.com.br'])
            ->getJson('/api/bookings/'.$bookingB->id)
            ->assertNotFound();
    }

    public function test_resposta_publica_nao_expoe_dados_pessoais(): void
    {
        [$tenant, $user] = $this->tenantWithUser();

        $this->runInTenant($tenant, function () use ($user): void {
            TenantConfig::query()->create();
            Booking::query()->create([
                'user_id' => $user->id,
                'event_date' => '2026-07-29',
                'period_type' => 'regular',
                'total_price' => 300,
                'status' => BookingStatus::CONFIRMED,
                'payment_expires_at' => now()->addDays(2),
            ]);
        });

        $response = $this->withServerVariables(['HTTP_HOST' => $tenant->subdomain.'.saas.com.br'])
            ->getJson('/api/public/calendar?month=2026-07');

        $response->assertOk();
        $response->assertJsonMissingPath('0.user_id');
        $response->assertJsonMissing(['email' => $user->email]);
        $response->assertJsonFragment([
            'date' => '2026-07-29',
            'available' => false,
        ]);
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