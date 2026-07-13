<?php

namespace App\Modules\Payment\Exceptions;

use RuntimeException;

class PaymentActionNotAllowedException extends RuntimeException
{
    public static function bookingNotPending(): self
    {
        return new self('Only pending bookings can start a payment transaction.');
    }

    public static function bookingExpired(): self
    {
        return new self('The booking payment window has expired.');
    }
}