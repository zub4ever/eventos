<?php

namespace App\Modules\Payment\Actions;

use App\Enums\BookingStatus;
use App\Enums\TransactionStatus;
use App\Models\Booking;
use App\Models\Transaction;
use App\Modules\Payment\DTOs\CreatePaymentTransactionData;
use App\Modules\Payment\DTOs\GatewayTransactionRequestData;
use App\Modules\Payment\Exceptions\PaymentActionNotAllowedException;
use App\Modules\Payment\Exceptions\PaymentGatewayException;
use App\Modules\Payment\Factories\PaymentGatewayFactory;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CreatePaymentTransactionAction
{
    public function __construct(
        private readonly PaymentGatewayFactory $paymentGatewayFactory,
    ) {
    }

    public function execute(Booking $booking, CreatePaymentTransactionData $data): Transaction
    {
        if ($booking->status !== BookingStatus::PENDING) {
            throw PaymentActionNotAllowedException::bookingNotPending();
        }

        if ($booking->payment_expires_at === null || $booking->payment_expires_at->isPast()) {
            throw PaymentActionNotAllowedException::bookingExpired();
        }

        $transaction = DB::transaction(function () use ($booking, $data): Transaction {
            return Transaction::query()->create([
                'booking_id' => $booking->id,
                'gateway_name' => $this->paymentGatewayFactory->providerName(),
                'payment_method' => $data->paymentMethod,
                'status' => TransactionStatus::PENDING,
                'amount' => $booking->total_price,
                'expires_at' => $booking->payment_expires_at,
            ]);
        });

        try {
            $gatewayResult = $this->paymentGatewayFactory
                ->make()
                ->createTransaction(new GatewayTransactionRequestData(
                    booking: $booking,
                    transaction: $transaction,
                    paymentMethod: $data->paymentMethod,
                    amount: (float) $booking->total_price,
                    expiresAt: CarbonImmutable::instance($booking->payment_expires_at),
                ));
        } catch (PaymentGatewayException $exception) {
            $transaction->forceFill([
                'status' => TransactionStatus::FAILED,
            ])->save();

            throw $exception;
        }

        $transaction->forceFill([
            'gateway_transaction_id' => $gatewayResult->externalId,
            'status' => $gatewayResult->status,
            'pix_qr_code' => $gatewayResult->pixQrCode,
            'pix_copy_paste' => $gatewayResult->pixCopyPaste,
            'boleto_url' => $gatewayResult->boletoUrl,
            'expires_at' => $gatewayResult->expiresAt ?? $booking->payment_expires_at,
            'gateway_payload' => $gatewayResult->payload,
        ])->save();

        return $transaction->refresh();
    }
}