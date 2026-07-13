<?php

namespace App\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\CalendarBlockFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarBlock extends Model
{
    /** @use HasFactory<CalendarBlockFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'blocked_date',
        'reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'blocked_date' => 'date',
        ];
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
