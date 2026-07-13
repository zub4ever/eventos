<?php

namespace App\Modules\Payment\Events;

use App\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Transaction $transaction,
        public array $payload,
        public string $provider,
        public ?string $eventType,
    ) {
    }
}