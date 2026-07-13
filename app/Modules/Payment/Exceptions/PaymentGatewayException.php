<?php

namespace App\Modules\Payment\Exceptions;

use RuntimeException;
use Throwable;

class PaymentGatewayException extends RuntimeException
{
    public static function requestFailed(string $provider, Throwable $previous): self
    {
        return new self("Payment gateway [{$provider}] request failed.", 0, $previous);
    }
}