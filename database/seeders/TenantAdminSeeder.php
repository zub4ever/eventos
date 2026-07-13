<?php

namespace Database\Seeders;

use App\Enums\TenantStatus;
use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\TenantConfig;
use App\Models\User;
use App\Modules\Tenancy\Support\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantAdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tenant = Tenant::query()->updateOrCreate(
            ['subdomain' => 'cliente1'],
            [
                'name' => 'Cliente 1',
                'status' => TenantStatus::ACTIVE,
            ]
        );

        app(TenantContext::class)->run($tenant, function (): void {
            TenantConfig::query()->updateOrCreate(
                [],
                [
                    'regular_price' => 300.00,
                    'extended_price' => 500.00,
                    'theme_color_hex' => '#0f172a',
                    'logo_url' => null,
                ]
            );

            User::query()->updateOrCreate(
                [
                    'email' => 'admin@cliente1.saas.com.br',
                ],
                [
                    'name' => 'Admin Cliente 1',
                    'password' => Hash::make('password'),
                    'role' => UserRole::ADMIN,
                    'phone' => null,
                ]
            );
        });
    }
}
