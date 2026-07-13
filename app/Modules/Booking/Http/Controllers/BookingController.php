<?php

namespace App\Modules\Booking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Modules\Booking\Actions\CancelBookingAction;
use App\Modules\Booking\Actions\CreateBookingAction;
use App\Modules\Booking\Exceptions\BookingDateUnavailableException;
use App\Modules\Booking\Http\Requests\StoreBookingRequest;
use App\Modules\Booking\Http\Resources\BookingResource;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request, CreateBookingAction $action): JsonResponse|BookingResource
    {
        $this->authorize('create', Booking::class);

        try {
            $booking = $action->execute($request->bookingData());
        } catch (BookingDateUnavailableException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 409);
        }

        return BookingResource::make($booking)->response()->setStatusCode(201);
    }

    public function show(Booking $booking): BookingResource
    {
        $this->authorize('view', $booking);

        return BookingResource::make($booking);
    }

    public function cancel(Booking $booking, CancelBookingAction $action): BookingResource
    {
        $this->authorize('cancel', $booking);

        return BookingResource::make($action->execute($booking));
    }
}