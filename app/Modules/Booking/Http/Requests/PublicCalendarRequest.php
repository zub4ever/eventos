<?php

namespace App\Modules\Booking\Http\Requests;

use App\Modules\Booking\DTOs\CalendarMonthData;
use Illuminate\Foundation\Http\FormRequest;

class PublicCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'date_format:Y-m'],
        ];
    }

    public function monthData(): CalendarMonthData
    {
        return CalendarMonthData::fromMonthString($this->validated('month'));
    }
}