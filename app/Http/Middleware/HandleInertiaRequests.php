<?php

namespace App\Http\Middleware;

use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props shared by default in every Inertia response.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $tenantContext = app(TenantContext::class);

        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'appDomain' => config('app.domain'),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'phone' => $request->user()->phone,
                    'role' => $request->user()->role?->value,
                ] : null,
            ],
            'tenantContext' => $tenantContext->hasTenant() ? [
                'id' => $tenantContext->id(),
                'subdomain' => $tenantContext->tenant()?->subdomain,
                'name' => $tenantContext->tenant()?->name,
            ] : null,
        ];
    }
}
