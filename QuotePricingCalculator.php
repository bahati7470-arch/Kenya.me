<?php

namespace App\Services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class QuotePricingCalculator
{
    public function total(string $quantity, string $unitPrice, string $markupPercent, string $discountPercent): string
    {
        $markupFactor = BigDecimal::of('100')->plus($markupPercent);
        $discountFactor = BigDecimal::of('100')->minus($discountPercent);

        return BigDecimal::of($quantity)
            ->multipliedBy($unitPrice)
            ->multipliedBy($markupFactor)
            ->multipliedBy($discountFactor)
            ->dividedBy('10000', 2, RoundingMode::HALF_UP)
            ->__toString();
    }
}