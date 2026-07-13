<?php

namespace App\Modules\Payment\Services;

use App\Enums\BookingStatus;
use App\Enums\TransactionStatus;
use App\Models\Booking;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Modules\Booking\Events\BookingConfirmed;
use App\Modules\Payment\DTOs\WebhookResultData;
use App\Modules\Payment\Events\PaymentReceived;
use App\Modules\Payment\Exceptions\InvalidWebhookSignatureException;
use App\Modules\Payment\Exceptions\WebhookTransactionNotFoundException;
use App\Modules\Payment\Factories\PaymentGatewayFactory;
use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPaymentWebhookService
{
    public function __construct(
        private readonly PaymentGatewayFactory $paymentGatewayFactory,
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function process(string $provider, Request $request): WebhookResultData
    {
        $gateway = $this->paymentGatewayFactory->make($provider);

        if (! $gateway->validateWebhook($request)) {
            throw new InvalidWebhookSignatureException();
        }

        $result = $gateway->processWebhook($request->all());

        if (! $result->knownEvent) {
            Log::info('Ignoring unknown payment webhook event', [
                'provider' => $provider,
                'event_type' => $result->eventType,
                'payload' => $result->rawPayload,
            ]);

            return $result;
        }

        if ($result->externalTransactionId === null) {
            Log::warning('Webhook without gateway transaction id', [
                'provider' => $provider,
                'payload' => $result->rawPayload,
            ]);

            return $result;
        }

        $transaction = Transaction::withoutGlobalScopes()
            ->where('gateway_transaction_id', $result->externalTransactionId)
            ->first();

        if ($transaction === null) {
            throw new WebhookTransactionNotFoundException($result->externalTransactionId);
        }

        $tenant = Tenant::query()->findOrFail($transaction->tenant_id);

        return $this->tenantContext->run($tenant, function () use ($provider, $transaction, $result): WebhookResultData {
            return DB::transaction(function () use ($provider, $transaction, $result): WebhookResultData {
                /** @var Transaction $scopedTransaction */
                $scopedTransaction = Transaction::query()->findOrFail($transaction->getKey());

                $paymentJustReceived = $this->synchronizeTransaction($scopedTransaction, $result);

                if ($paymentJustReceived) {
                    event(new PaymentReceived($scopedTransaction, $result->rawPayload, $provider, $result->eventType));
                }

                return $result;
            });
        });
    }

    private function synchronizeTransaction(Transaction $transaction, WebhookResultData $result): bool
    {
        $paymentJustReceived = $transaction->status !== TransactionStatus::PAID
            && $result->status === TransactionStatus::PAID;

        $updates = [];

        if ($result->status !== null && $transaction->status !== $result->status) {
            $updates['status'] = $result->status;
        }

        if ($result->paidAt !== null && $transaction->paid_at?->equalTo($result->paidAt) !== true) {
            $updates['paid_at'] = $result->paidAt;
        }

        if ($updates !== []) {
            $updates['gateway_payload'] = $result->rawPayload;
            $transaction->forceFill($updates)->save();
        }

        if ($result->status === TransactionStatus::PAID) {
            /** @var Booking $booking */
            $booking = $transaction->booking()->firstOrFail();

            if ($booking->status === BookingStatus::PENDING) {
                $booking->forceFill([
                    'status' => BookingStatus::CONFIRMED,
                ])->save();

                event(new BookingConfirmed($booking, $transaction));
            }
        }

        return $paymentJustReceived;
    }
}