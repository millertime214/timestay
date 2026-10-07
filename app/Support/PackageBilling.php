<?php

namespace App\Support;

use Carbon\Carbon;
use InvalidArgumentException;

class PackageBilling
{
    public const TERMS = [
        'monthly' => ['label' => 'Monthly', 'months' => 1],
        'semi-annual' => ['label' => 'Semi-Annual', 'months' => 6],
        'yearly' => ['label' => 'Annual', 'months' => 12],
    ];

    public static function months(string $term): int
    {
        if (!isset(self::TERMS[$term])) {
            throw new InvalidArgumentException('Invalid package billing term.');
        }

        return self::TERMS[$term]['months'];
    }

    public static function subtotal($monthlyPrice, string $term): float
    {
        return round((float) $monthlyPrice * self::months($term), 2);
    }

    public static function total($monthlyPrice, string $term, $discountPercent = 0): float
    {
        $subtotal = self::subtotal($monthlyPrice, $term);
        $discount = min(max((float) $discountPercent, 0), 100);

        return round($subtotal * (1 - ($discount / 100)), 2);
    }

    public static function expiresAt(Carbon $startDate, string $term): Carbon
    {
        return $startDate->copy()->addMonthsNoOverflow(self::months($term))->subDay();
    }
}
