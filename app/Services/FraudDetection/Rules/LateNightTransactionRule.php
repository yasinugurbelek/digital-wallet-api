<?php

namespace App\Services\FraudDetection\Rules;

use App\Enums\Currency;
use Closure;

class LateNightTransactionRule
{
    public function handle(array $payload, Closure $next)
    {
        $data = $payload['data'];
        $currentHour = now()->hour;
        
        if ($currentHour >= 2 && $currentHour <= 6) {
            $currency = Currency::from($data['currency']);
            $amountTRY = $currency->toTRY($data['amount']);
            
            if ($amountTRY >= 5000) {
                $payload['flagged'] = true;
                $payload['reasons'][] = '5,000+ TRY transaction between 02:00-06:00';
            }
        }
        
        return $next($payload);
    }
}
