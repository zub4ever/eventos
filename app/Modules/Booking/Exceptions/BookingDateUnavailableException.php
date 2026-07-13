<?php

namespace App\Modules\Booking\Exceptions;

use RuntimeException;

class BookingDateUnavailableException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The requested date is not available.');
    }
}