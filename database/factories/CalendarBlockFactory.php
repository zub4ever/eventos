<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\CalendarBlock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarBlock>
 */
class CalendarBlockFactory extends Factory
{
    protected $model = CalendarBlock::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'blocked_date' => fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'reason' => fake()->optional()->sentence(),
            'created_by' => User::factory()->admin(),
        ];
    }
}
