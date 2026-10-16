<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMatchAttempt extends Model
{
    protected $fillable = [
        'mpesa_payment_id',
        'invoice_id',
        'strategy',
        'confidence',
        'outcome',
    ];

    protected function casts(): array
    {
        return ['confidence' => 'decimal:2'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(MpesaPayment::class, 'mpesa_payment_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}