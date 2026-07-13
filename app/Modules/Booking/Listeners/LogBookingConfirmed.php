<?php

namespace App\Modules\Booking\Listeners;

use App\Modules\Booking\Events\BookingConfirmed;
use Illuminate\Support\Facades\Log;

class LogBookingConfirmed
{
    public function handle(BookingConfirmed $event): void
    {
        Log::info('Booking confirmed', [
            'booking_id' => $event->booking->id,
            'transaction_id' => $event->transaction->id,
        ]);
    }
}