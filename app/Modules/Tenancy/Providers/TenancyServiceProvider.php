<?php

namespace App\Modules\Tenancy\Providers;

use App\Modules\Tenancy\Scopes\TenantScope;
use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);

        $this->app->singleton(TenantScope::class, function ($app): TenantScope {
            return new TenantScope($app->make(TenantContext::class));
        });
    }
}