<?php

namespace App\Modules\Booking\Services;

use App\Enums\BookingPeriodType;
use App\Models\TenantConfig;

class BookingPricingService
{
    public function priceFor(BookingPeriodType $periodType): float
    {
        $config = TenantConfig::query()->firstOrCreate([]);

        return match ($periodType) {
            BookingPeriodType::REGULAR => (float) $config->regular_price,
            BookingPeriodType::EXTENDED => (float) $config->extended_price,
        };
    }
}