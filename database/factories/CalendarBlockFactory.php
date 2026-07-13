<?php

namespace Database\Factories;

use App\Models\CalendarBlock;
use App\Models\Tenant;
use App\Models\User;
use App\Enums\UserRole;
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
            'tenant_id' => Tenant::factory(),
            'blocked_date' => fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'reason' => fake()->optional()->sentence(),
            'created_by' => fn (array $attributes) => User::factory()->create([
                'tenant_id' => $attributes['tenant_id'],
                'role' => UserRole::ADMIN,
            ])->id,
        ];
    }
}
