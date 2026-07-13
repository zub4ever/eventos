<?php

namespace App\Modules\Booking\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;

class CancelBookingAction
{
    public function execute(Booking $booking): Booking
    {
        if ($booking->status !== BookingStatus::PENDING) {
            throw ValidationException::withMessages([
                'booking' => 'Only pending bookings can be canceled.',
            ]);
        }

        $booking->forceFill([
            'status' => BookingStatus::CANCELED,
        ])->save();

        return $booking->refresh();
    }
}