<?php

namespace App\Modules\Booking\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\CalendarBlock;
use App\Modules\Booking\DTOs\CalendarMonthData;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class BookingAvailabilityService
{
    public function isDateAvailable(CarbonInterface $date): bool
    {
        $formattedDate = CarbonImmutable::instance($date)->toDateString();

        $hasBlockingBooking = Booking::query()
            ->whereDate('event_date', $formattedDate)
            ->whereIn('status', [BookingStatus::PENDING, BookingStatus::CONFIRMED])
            ->exists();

        if ($hasBlockingBooking) {
            return false;
        }

        return ! CalendarBlock::query()
            ->whereDate('blocked_date', $formattedDate)
            ->exists();
    }

    public function monthlyAvailability(CalendarMonthData $monthData): Collection
    {
        $unavailableDates = $this->unavailableDates($monthData)
            ->flip();

        $days = collect();
        $cursor = $monthData->startOfMonth;

        while ($cursor->lte($monthData->endOfMonth)) {
            $dateString = $cursor->toDateString();
            $days->push([
                'date' => $dateString,
                'available' => ! $unavailableDates->has($dateString),
            ]);
            $cursor = $cursor->addDay();
        }

        return $days;
    }

    public function unavailableDates(CalendarMonthData $monthData): Collection
    {
        $bookingDates = Booking::query()
            ->whereBetween('event_date', [$monthData->startOfMonth->toDateString(), $monthData->endOfMonth->toDateString()])
            ->whereIn('status', [BookingStatus::PENDING, BookingStatus::CONFIRMED])
            ->pluck('event_date')
            ->map(fn ($date) => CarbonImmutable::parse($date)->toDateString());

        $blockedDates = CalendarBlock::query()
            ->whereBetween('blocked_date', [$monthData->startOfMonth->toDateString(), $monthData->endOfMonth->toDateString()])
            ->pluck('blocked_date')
            ->map(fn ($date) => CarbonImmutable::parse($date)->toDateString());

        return $bookingDates->merge($blockedDates)->unique()->values();
    }
}