<?php

namespace App\Modules\PublicPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TenantConfig;
use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPortalController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function show(Request $request): Response
    {
        if (! $this->tenantContext->hasTenant()) {
            return Inertia::render('Home', [
                'appName' => config('app.name'),
                'appDomain' => config('app.domain'),
            ]);
        }

        $tenant = $this->tenantContext->tenant()->loadMissing('config');
        $config = $tenant->config ?? new TenantConfig([
            'regular_price' => 300.00,
            'extended_price' => 500.00,
            'theme_color_hex' => '#0f766e',
            'logo_url' => null,
        ]);

        return Inertia::render('PublicPortal', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
                'logoUrl' => $config->logo_url,
                'themeColor' => $config->theme_color_hex ?: '#0f766e',
                'prices' => [
                    'regular' => (float) $config->regular_price,
                    'extended' => (float) $config->extended_price,
                ],
            ],
        ]);
    }
}