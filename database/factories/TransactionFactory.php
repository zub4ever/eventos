<?php

namespace Database\Factories;

use App\Enums\TransactionPaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\Booking;
use App\Models\Tenant;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'booking_id' => fn (array $attributes) => Booking::factory()->create([
                'tenant_id' => $attributes['tenant_id'],
            ])->id,
            'gateway_name' => 'abacatepay',
            'gateway_transaction_id' => fake()->optional()->uuid(),
            'payment_method' => fake()->randomElement(TransactionPaymentMethod::cases()),
            'status' => TransactionStatus::PENDING,
            'amount' => fake()->randomElement([300.00, 500.00]),
            'pix_qr_code' => null,
            'pix_copy_paste' => null,
            'boleto_url' => null,
            'expires_at' => now()->addHours(2),
            'paid_at' => null,
            'gateway_payload' => null,
        ];
    }
}
