<?php

namespace App\Modules\Booking\DTOs;

use App\Enums\BookingPeriodType;
use Carbon\CarbonImmutable;

readonly class CreateBookingData
{
    public function __construct(
        public string $userId,
        public CarbonImmutable $eventDate,
        public BookingPeriodType $periodType,
    ) {
    }
}