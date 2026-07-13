<?php

namespace Database\Factories;

use App\Enums\BookingPeriodType;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            'period_type' => fake()->randomElement(BookingPeriodType::cases()),
            'total_price' => fake()->randomElement([300.00, 500.00]),
            'status' => BookingStatus::PENDING,
            'payment_expires_at' => now()->addDay(),
        ];
    }
}
