<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceReferenceGenerator
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function generate(): string
    {
        do {
            $suffix = '';

            for ($index = 0; $index < 8; $index++) {
                $suffix .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }

            $reference = 'KME-'.now()->format('ym').'-'.$suffix;
        } while (Invoice::query()->where('mpesa_reference', $reference)->exists());

        return $reference;
    }
}