<?php

namespace App\Modules\Tenancy\Exceptions;

use LogicException;

class MissingTenantContextException extends LogicException
{
    public static function forModel(string $modelClass): self
    {
        return new self("Cannot create [{$modelClass}] without an active tenant context.");
    }
}