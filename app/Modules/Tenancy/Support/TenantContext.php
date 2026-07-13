<?php

namespace App\Modules\Tenancy\Support;

use App\Models\Tenant;
use Closure;

class TenantContext
{
    private ?Tenant $tenant = null;

    public function setTenant(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?string
    {
        return $this->tenant?->getKey();
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }

    public function run(Tenant $tenant, Closure $callback): mixed
    {
        $previousTenant = $this->tenant;

        $this->setTenant($tenant);

        try {
            return $callback();
        } finally {
            $this->tenant = $previousTenant;
        }
    }
}