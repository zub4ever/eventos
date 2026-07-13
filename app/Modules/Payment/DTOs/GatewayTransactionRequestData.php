<?php

namespace App\Modules\Payment\DTOs;

use App\Enums\TransactionPaymentMethod;
use App\Models\Booking;
use App\Models\Transaction;
use Carbon\CarbonImmutable;

readonly class GatewayTransactionRequestData
{
    public function __construct(
        public Booking $booking,
        public Transaction $transaction,
        public TransactionPaymentMethod $paymentMethod,
        public float $amount,
        public CarbonImmutable $expiresAt,
    ) {
    }
}