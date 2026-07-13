<?php

namespace Tests\Feature\Tenancy;

use App\Enums\TenantStatus;
use App\Models\Booking;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SubdomainTenancyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.domain', 'saas.com.br');
        config()->set('tenancy.domain', 'saas.com.br');
        config()->set('tenancy.central_domains', [
            'saas.com.br',
            'www.saas.com.br',
            'api.saas.com.br',
        ]);

        Route::middleware('web')->get('/tenant-bookings', function () {
            return response()->json([
                'booking_ids' => Booking::query()->pluck('id')->all(),
            ]);
        });

        Route::middleware('web')->get('/admin-only', function () {
            return response()->json(['ok' => true]);
        });
    }

    public function test_tenant_a_cannot_access_records_from_tenant_b(): void
    {
        [$tenantA, $tenantB] = Tenant::factory()->count(2)->create()->all();

        $bookingA = $this->runInTenant($tenantA, fn () => Booking::factory()->create());
        $bookingB = $this->runInTenant($tenantB, fn () => Booking::factory()->create());

        $response = $this->withServerVariables([
            'HTTP_HOST' => $tenantA->subdomain.'.saas.com.br',
        ])->get('/tenant-bookings');

        $response->assertOk();
        $response->assertJson([
            'booking_ids' => [$bookingA->id],
        ]);
        $response->assertJsonMissing([
            'booking_ids' => [$bookingB->id],
        ]);
    }

    public function test_creation_automatically_fills_tenant_id(): void
    {
        $tenant = Tenant::factory()->create();

        $booking = $this->runInTenant($tenant, fn () => Booking::factory()->create());

        $this->assertSame($tenant->id, $booking->tenant_id);
    }

    public function test_unknown_tenant_returns_not_found(): void
    {
        $this->withServerVariables([
            'HTTP_HOST' => 'inexistente.saas.com.br',
        ])->get('/')->assertNotFound();
    }

    public function test_inactive_tenant_is_forbidden(): void
    {
        $tenant = Tenant::factory()->create([
            'status' => TenantStatus::INACTIVE,
        ]);

        $this->withServerVariables([
            'HTTP_HOST' => $tenant->subdomain.'.saas.com.br',
        ])->get('/')->assertForbidden();
    }

    public function test_central_domains_do_not_resolve_tenant(): void
    {
        $response = $this->withServerVariables([
            'HTTP_HOST' => 'saas.com.br',
        ])->get('/');

        $response->assertOk();
        $this->assertFalse(app(TenantContext::class)->hasTenant());
    }

    public function test_user_from_another_tenant_cannot_access_application(): void
    {
        $tenantA = Tenant::factory()->create(['subdomain' => 'cliente-a']);
        $tenantB = Tenant::factory()->create(['subdomain' => 'cliente-b']);
        $userFromTenantB = $this->runInTenant($tenantB, fn () => User::factory()->admin()->create());

        $response = $this->actingAs($userFromTenantB)
            ->withServerVariables([
                'HTTP_HOST' => $tenantA->subdomain.'.saas.com.br',
            ])
            ->get('/admin-only');

        $response->assertForbidden();
    }

    private function runInTenant(Tenant $tenant, callable $callback): mixed
    {
        return app(TenantContext::class)->run($tenant, $callback(...));
    }
}