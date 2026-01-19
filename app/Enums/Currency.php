<?php

namespace App\Enums;

enum Currency: string
{
    case TRY = 'TRY';
    case USD = 'USD';
    case EUR = 'EUR';

    public function toTRY(float $amount): float
    {
        return match($this) {
            self::TRY => $amount,
            self::USD => $amount * 43.0,
            self::EUR => $amount * 50.0,
        };
    }
}
