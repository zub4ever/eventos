<?php

namespace App\Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Modules\Payment\Actions\CreatePaymentTransactionAction;
use App\Modules\Payment\Exceptions\InvalidPaymentProviderException;
use App\Modules\Payment\Exceptions\PaymentActionNotAllowedException;
use App\Modules\Payment\Exceptions\PaymentGatewayException;
use App\Modules\Payment\Http\Requests\StoreBookingPaymentRequest;
use App\Modules\Payment\Http\Resources\PaymentTransactionResource;
use Illuminate\Http\JsonResponse;

class BookingPaymentController extends Controller
{
    public function store(
        StoreBookingPaymentRequest $request,
        Booking $booking,
        CreatePaymentTransactionAction $action,
    ): JsonResponse|PaymentTransactionResource {
        $this->authorize('pay', $booking);

        try {
            $transaction = $action->execute($booking, $request->paymentData());
        } catch (PaymentActionNotAllowedException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (InvalidPaymentProviderException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (PaymentGatewayException $exception) {
            return response()->json(['message' => $exception->getMessage()], 502);
        }

        return PaymentTransactionResource::make($transaction)->response()->setStatusCode(201);
    }
}