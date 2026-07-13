<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantConfig>
 */
class TenantConfigFactory extends Factory
{
    protected $model = TenantConfig::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'regular_price' => 300.00,
            'extended_price' => 500.00,
            'theme_color_hex' => fake()->optional()->hexColor(),
            'logo_url' => fake()->optional()->imageUrl(),
        ];
    }
}
