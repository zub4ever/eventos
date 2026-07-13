<?php

namespace App\Models;

use Database\Factories\TenantConfigFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantConfig extends Model
{
    /** @use HasFactory<TenantConfigFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
