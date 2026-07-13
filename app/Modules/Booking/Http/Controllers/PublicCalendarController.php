<?php

namespace App\Modules\Booking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Http\Requests\PublicCalendarRequest;
use App\Modules\Booking\Http\Resources\CalendarAvailabilityResource;
use App\Modules\Booking\Services\BookingAvailabilityService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicCalendarController extends Controller
{
    public function index(
        PublicCalendarRequest $request,
        BookingAvailabilityService $availabilityService,
    ): AnonymousResourceCollection {
        return CalendarAvailabilityResource::collection(
            $availabilityService->monthlyAvailability($request->monthData())
        );
    }
}