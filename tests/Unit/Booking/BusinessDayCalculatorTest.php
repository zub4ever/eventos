<?php

namespace Tests\Unit\Booking;

use App\Modules\Booking\Services\BusinessDayCalculator;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class BusinessDayCalculatorTest extends TestCase
{
    public function test_adds_two_business_days(): void
    {
        $calculator = app(BusinessDayCalculator::class);

        $result = $calculator->addBusinessDays(CarbonImmutable::parse('2026-07-13 10:00:00'), 2);

        $this->assertSame('2026-07-15 10:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function test_skips_weekend_when_adding_business_days(): void
    {
        $calculator = app(BusinessDayCalculator::class);

        $result = $calculator->addBusinessDays(CarbonImmutable::parse('2026-07-17 10:00:00'), 2);

        $this->assertSame('2026-07-21 10:00:00', $result->format('Y-m-d H:i:s'));
    }
}