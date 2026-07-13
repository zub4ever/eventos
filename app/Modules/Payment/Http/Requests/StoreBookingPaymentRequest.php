<?php

namespace App\Modules\Payment\Http\Requests;

use App\Enums\TransactionPaymentMethod;
use App\Modules\Payment\DTOs\CreatePaymentTransactionData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::enum(TransactionPaymentMethod::class)],
        ];
    }

    public function paymentData(): CreatePaymentTransactionData
    {
        return new CreatePaymentTransactionData(
            paymentMethod: TransactionPaymentMethod::from($this->validated('payment_method')),
        );
    }
}