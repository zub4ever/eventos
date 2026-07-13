<?php

namespace App\Modules\Booking\Listeners;

use App\Modules\Booking\Events\BookingCanceled;
use Illuminate\Support\Facades\Log;

class LogBookingCanceled
{
    public function handle(BookingCanceled $event): void
    {
        Log::info('Booking canceled', [
            'booking_id' => $event->booking->id,
            'reason' => $event->reason,
        ]);
    }
}