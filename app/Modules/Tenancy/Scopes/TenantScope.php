<?php

namespace App\Modules\Tenancy\Scopes;

use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function apply(Builder $builder, Model $model): void
    {
        if (! $this->tenantContext->hasTenant()) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($model->qualifyColumn('tenant_id'), $this->tenantContext->id());
    }
}