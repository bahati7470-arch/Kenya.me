<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    protected $fillable = [
        'business_id',
        'customer_id',
        'invoice_id',
        'quote_number',
        'description',
        'quantity',
        'unit_price',
        'markup_percent',
        'discount_percent',
        'amount',
        'status',
        'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'markup_percent' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'amount' => 'decimal:2',
            'valid_until' => 'date',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}