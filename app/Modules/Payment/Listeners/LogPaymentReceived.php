<?php

namespace App\Modules\Payment\Listeners;

use App\Modules\Payment\Events\PaymentReceived;
use Illuminate\Support\Facades\Log;

class LogPaymentReceived
{
    public function handle(PaymentReceived $event): void
    {
        Log::info('Payment received', [
            'transaction_id' => $event->transaction->id,
            'provider' => $event->provider,
            'event_type' => $event->eventType,
        ]);
    }
}