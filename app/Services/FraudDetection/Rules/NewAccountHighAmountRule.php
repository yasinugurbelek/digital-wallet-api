<?php

namespace App\Services\FraudDetection\Rules;

use App\Enums\Currency;
use Closure;

class NewAccountHighAmountRule
{
    public function handle(array $payload, Closure $next)
    {
        $user = $payload['user'];
        $data = $payload['data'];
        
        if ($user->isNewAccount()) {
            $currency = Currency::from($data['currency']);
            $amountTRY = $currency->toTRY($data['amount']);
            
            if ($amountTRY >= 10000) {
                $payload['flagged'] = true;
                $payload['reasons'][] = 'New account (7 days) + 10,000+ TRY transaction';
            }
        }
        
        return $next($payload);
    }
}
