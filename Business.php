<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'paybill_shortcode',
        'webhook_token',
        'daraja_consumer_key',
        'daraja_consumer_secret',
        'daraja_registered_at',
    ];

    protected function casts(): array
    {
        return [
            'daraja_consumer_key' => 'encrypted',
            'daraja_consumer_secret' => 'encrypted',
            'daraja_registered_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MpesaPayment::class);
    }
}