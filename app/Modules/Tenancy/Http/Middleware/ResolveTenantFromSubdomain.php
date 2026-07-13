<?php

namespace App\Modules\Tenancy\Http\Middleware;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Modules\Tenancy\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromSubdomain
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->tenantContext->clear();

        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);

        if ($this->shouldUseLocalDefaultTenant($host)) {
            $tenant = $this->findActiveTenant(config('tenancy.local_default_subdomain'));

            if ($tenant !== null) {
                $this->tenantContext->setTenant($tenant);

                try {
                    return $next($request);
                } finally {
                    $this->tenantContext->clear();
                }
            }
        }

        if (in_array($host, $centralDomains, true)) {
            try {
                return $next($request);
            } finally {
                $this->tenantContext->clear();
            }
        }

        $subdomain = $this->extractSubdomain($host);

        if ($subdomain === null) {
            abort(404);
        }

        $tenant = $this->findActiveTenant($subdomain, validateStatus: false);

        if ($tenant === null) {
            abort(404);
        }

        if ($tenant->status !== TenantStatus::ACTIVE) {
            abort(403);
        }

        $this->tenantContext->setTenant($tenant);

        try {
            return $next($request);
        } finally {
            $this->tenantContext->clear();
        }
    }

    private function extractSubdomain(string $host): ?string
    {
        $domain = config('tenancy.domain');

        if (! is_string($domain) || $domain === '') {
            return null;
        }

        $suffix = '.'.$domain;

        if (! str_ends_with($host, $suffix)) {
            return null;
        }

        $subdomain = substr($host, 0, -strlen($suffix));

        return $subdomain !== '' ? $subdomain : null;
    }

    private function shouldUseLocalDefaultTenant(string $host): bool
    {
        return app()->environment('local')
            && in_array($host, ['localhost', '127.0.0.1'], true)
            && is_string(config('tenancy.local_default_subdomain'))
            && config('tenancy.local_default_subdomain') !== '';
    }

    private function findActiveTenant(?string $subdomain, bool $validateStatus = true): ?Tenant
    {
        if (! is_string($subdomain) || $subdomain === '') {
            return null;
        }

        $tenant = Tenant::query()
            ->where('subdomain', $subdomain)
            ->first();

        if ($tenant === null) {
            return null;
        }

        if ($validateStatus && $tenant->status !== TenantStatus::ACTIVE) {
            return null;
        }

        return $tenant;
    }
}