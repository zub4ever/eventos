<?php

namespace Tests\Unit\Tenancy;

use App\Models\Booking;
use App\Models\Tenant;
use App\Modules\Tenancy\Exceptions\MissingTenantContextException;
use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_run_executes_callback_within_tenant_and_restores_previous_context(): void
    {
        $context = app(TenantContext::class);
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $context->setTenant($tenantA);

        $resolvedTenantId = $context->run($tenantB, function () use ($context): ?string {
            return $context->id();
        });

        $this->assertSame($tenantB->id, $resolvedTenantId);
        $this->assertSame($tenantA->id, $context->id());
    }

    public function test_creating_tenant_scoped_model_without_context_throws_exception(): void
    {
        $this->expectException(MissingTenantContextException::class);

        Booking::factory()->create();
    }
}