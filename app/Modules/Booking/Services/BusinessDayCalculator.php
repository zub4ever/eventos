<?php

namespace App\Modules\Booking\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class BusinessDayCalculator
{
    public function addBusinessDays(CarbonInterface $startDate, int $days = 2): CarbonImmutable
    {
        $date = CarbonImmutable::instance($startDate);
        $remainingDays = $days;

        while ($remainingDays > 0) {
            $date = $date->addDay();

            if ($date->isWeekend()) {
                continue;
            }

            $remainingDays--;
        }

        return $date;
    }
}