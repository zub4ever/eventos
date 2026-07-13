<?php

namespace App\Modules\Booking\DTOs;

use Carbon\CarbonImmutable;

readonly class CalendarMonthData
{
    public function __construct(
        public string $month,
        public CarbonImmutable $startOfMonth,
        public CarbonImmutable $endOfMonth,
    ) {
    }

    public static function fromMonthString(string $month): self
    {
        $startOfMonth = CarbonImmutable::createFromFormat('Y-m', $month)->startOfMonth();

        return new self(
            month: $month,
            startOfMonth: $startOfMonth,
            endOfMonth: $startOfMonth->endOfMonth(),
        );
    }
}