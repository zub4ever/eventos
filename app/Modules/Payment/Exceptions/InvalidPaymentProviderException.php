<?php

namespace App\Modules\Payment\Exceptions;

use InvalidArgumentException;

class InvalidPaymentProviderException extends InvalidArgumentException
{
    public static function unsupported(string $provider): self
    {
        return new self("Unsupported payment provider [{$provider}].");
    }
}