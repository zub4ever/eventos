<?php

namespace App\Modules\Payment\Exceptions;

use RuntimeException;

class WebhookTransactionNotFoundException extends RuntimeException
{
    public function __construct(string $externalTransactionId)
    {
        parent::__construct("Transaction not found for gateway transaction id [{$externalTransactionId}].");
    }
}