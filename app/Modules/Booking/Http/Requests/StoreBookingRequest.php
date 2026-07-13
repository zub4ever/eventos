<?php

namespace App\Modules\Booking\Http\Requests;

use App\Enums\BookingPeriodType;
use App\Models\User;
use App\Modules\Booking\DTOs\CreateBookingData;
use App\Modules\Tenancy\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->id();

        return [
            'user_id' => [
                'nullable',
                'uuid',
                Rule::exists(User::class, 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'period_type' => ['required', Rule::enum(BookingPeriodType::class)],
        ];
    }

    public function bookingData(): CreateBookingData
    {
        return new CreateBookingData(
            userId: $this->validated('user_id') ?? (string) $this->user()->getKey(),
            eventDate: CarbonImmutable::parse($this->validated('event_date'))->startOfDay(),
            periodType: BookingPeriodType::from($this->validated('period_type')),
        );
    }
}