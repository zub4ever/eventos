<?php

namespace App\Modules\Tenancy\Http\Middleware;

use App\Modules\Tenancy\Exceptions\TenantAccessDeniedException;
use App\Modules\Tenancy\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticatedUserBelongsToTenant
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tenantContext->hasTenant()) {
            return $next($request);
        }

        $user = $request->user();

        if ($user !== null && (string) $user->tenant_id !== (string) $this->tenantContext->id()) {
            throw new TenantAccessDeniedException();
        }

        return $next($request);
    }
}