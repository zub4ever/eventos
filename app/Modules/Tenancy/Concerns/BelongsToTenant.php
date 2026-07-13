<?php

namespace App\Modules\Tenancy\Concerns;

use App\Models\Tenant;
use App\Modules\Tenancy\Exceptions\MissingTenantContextException;
use App\Modules\Tenancy\Exceptions\TenantAttributeImmutableException;
use App\Modules\Tenancy\Scopes\TenantScope;
use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(app(TenantScope::class));

        static::creating(function (Model $model): void {
            $tenantContext = app(TenantContext::class);

            if (! $tenantContext->hasTenant()) {
                throw MissingTenantContextException::forModel($model::class);
            }

            $contextTenantId = $tenantContext->id();
            $modelTenantId = $model->getAttribute('tenant_id');

            if ($modelTenantId === null) {
                $model->setAttribute('tenant_id', $contextTenantId);

                return;
            }

            if ((string) $modelTenantId !== (string) $contextTenantId) {
                throw TenantAttributeImmutableException::forModel($model::class);
            }
        });

        static::updating(function (Model $model): void {
            if ($model->isDirty('tenant_id')) {
                throw TenantAttributeImmutableException::forModel($model::class);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}