<?php

namespace Tests\Unit\Booking;

use App\Enums\BookingPeriodType;
use App\Models\Tenant;
use App\Models\TenantConfig;
use App\Modules\Booking\Services\BookingPricingService;
use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_regular_price_from_tenant_config(): void
    {
        $tenant = Tenant::factory()->create();

        $price = $this->runInTenant($tenant, function () {
            TenantConfig::query()->create([
                'regular_price' => 350.00,
                'extended_price' => 550.00,
            ]);

            return app(BookingPricingService::class)->priceFor(BookingPeriodType::REGULAR);
        });

        $this->assertSame(350.0, $price);
    }

    public function test_returns_extended_price_from_tenant_config(): void
    {
        $tenant = Tenant::factory()->create();

        $price = $this->runInTenant($tenant, function () {
            TenantConfig::query()->create([
                'regular_price' => 350.00,
                'extended_price' => 550.00,
            ]);

            return app(BookingPricingService::class)->priceFor(BookingPeriodType::EXTENDED);
        });

        $this->assertSame(550.0, $price);
    }

    private function runInTenant(Tenant $tenant, callable $callback): mixed
    {
        return app(TenantContext::class)->run($tenant, $callback(...));
    }
}