<?php

namespace App\Modules\Payment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTransactionResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'gateway_name' => $this->gateway_name,
            'payment_method' => $this->payment_method?->value,
            'status' => $this->status?->value,
            'amount' => (float) $this->amount,
            'pix_qr_code' => $this->pix_qr_code,
            'pix_copy_paste' => $this->pix_copy_paste,
            'boleto_url' => $this->boleto_url,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'gateway_transaction_id' => $this->gateway_transaction_id,
        ];
    }
}