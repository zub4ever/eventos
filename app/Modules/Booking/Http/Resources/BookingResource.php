<?php

namespace App\Modules\Booking\Http\Resources;

use App\Enums\BookingPeriodType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $endHour = $this->period_type === BookingPeriodType::EXTENDED ? '22:00' : '19:00';

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'event_date' => $this->event_date?->toDateString(),
            'period_type' => $this->period_type?->value,
            'period_window' => [
                'start' => '09:00',
                'end' => $endHour,
            ],
            'total_price' => (float) $this->total_price,
            'status' => $this->status?->value,
            'payment_expires_at' => $this->payment_expires_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}