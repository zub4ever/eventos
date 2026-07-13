<?php

namespace App\Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Exceptions\InvalidPaymentProviderException;
use App\Modules\Payment\Exceptions\InvalidWebhookSignatureException;
use App\Modules\Payment\Exceptions\WebhookTransactionNotFoundException;
use App\Modules\Payment\Services\ProcessPaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function store(
        string $provider,
        Request $request,
        ProcessPaymentWebhookService $service,
    ): JsonResponse {
        try {
            $result = $service->process($provider, $request);
        } catch (InvalidPaymentProviderException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        } catch (InvalidWebhookSignatureException $exception) {
            return response()->json(['message' => $exception->getMessage()], 403);
        } catch (WebhookTransactionNotFoundException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        if (! $result->knownEvent) {
            return response()->json(['message' => 'Event ignored.'], 202);
        }

        return response()->json([
            'message' => 'Webhook processed.',
            'transaction_id' => $result->externalTransactionId,
            'event_type' => $result->eventType,
        ]);
    }
}