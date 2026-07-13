<?php

namespace App\Modules\Tenancy\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TenantAccessDeniedException extends HttpException
{
    public function __construct()
    {
        parent::__construct(403, 'Authenticated user does not belong to the resolved tenant.');
    }
}