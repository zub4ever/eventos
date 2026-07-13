<?php

namespace App\Modules\Tenancy\Exceptions;

use LogicException;

class TenantAttributeImmutableException extends LogicException
{
    public static function forModel(string $modelClass): self
    {
        return new self("The tenant_id attribute is immutable for [{$modelClass}].");
    }
}