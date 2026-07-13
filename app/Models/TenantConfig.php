<?php

namespace App\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\TenantConfigFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantConfig extends Model
{
    /** @use HasFactory<TenantConfigFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'regular_price',
        'extended_price',
        'theme_color_hex',
        'logo_url',
    ];

    protected function casts(): array
    {
        return [
            'regular_price' => 'decimal:2',
            'extended_price' => 'decimal:2',
        ];
    }
}
