<?php

namespace App\Modules\Payment\DTOs;

use App\Enums\TransactionPaymentMethod;

readonly class CreatePaymentTransactionData
{
    public function __construct(
        public TransactionPaymentMethod $paymentMethod,
    ) {
    }
}