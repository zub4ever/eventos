<?php

namespace App\Modules\Booking\Events;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCanceled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public string $reason,
    ) {
    }
}