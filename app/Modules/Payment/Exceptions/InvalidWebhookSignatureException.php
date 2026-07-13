<?php

namespace App\Modules\Payment\Exceptions;

use RuntimeException;

class InvalidWebhookSignatureException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Invalid webhook signature.');
    }
}