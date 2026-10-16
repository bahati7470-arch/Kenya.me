<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MpesaPayment extends Model
{
    protected $fillable = [
        'business_id',
        'invoice_id',
        'receipt_number',
        'amount',
        'phone',
        'bill_ref_number',
        'transacted_at',
        'status',
        'match_method',
        'match_confidence',
        'callback_payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'match_confidence' => 'decimal:2',
            'transacted_at' => 'datetime',
            'callback_payload' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function matchAttempts(): HasMany
    {
        return $this->hasMany(PaymentMatchAttempt::class);
    }
}