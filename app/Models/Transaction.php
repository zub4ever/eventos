<?php

namespace App\Models;

use App\Enums\TransactionPaymentMethod;
use App\Enums\TransactionStatus;
use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'booking_id',
        'gateway_name',
        'gateway_transaction_id',
        'payment_method',
        'status',
        'amount',
        'pix_qr_code',
        'pix_copy_paste',
        'boleto_url',
        'expires_at',
        'paid_at',
        'gateway_payload',
    ];

    protected function casts(): array
    {
        return [
            'payment_method' => TransactionPaymentMethod::class,
            'status' => TransactionStatus::class,
            'amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'gateway_payload' => 'array',
        ];
    }
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
