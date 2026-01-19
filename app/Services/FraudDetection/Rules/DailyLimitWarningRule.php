<?php

namespace App\Services\FraudDetection\Rules;

use App\Models\Transaction;
use App\Enums\Currency;
use Closure;

class DailyLimitWarningRule
{
    public function handle(array $payload, Closure $next)
    {
        $user = $payload['user'];
        $data = $payload['data'];
        
        $todayTotal = Transaction::where('from_user_id', $user->id)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('amount');
        
        $currency = Currency::from($data['currency']);
        $todayTotalTRY = $currency->toTRY($todayTotal);
        $currentAmountTRY = $currency->toTRY($data['amount']);
        
        $newTotal = $todayTotalTRY + $currentAmountTRY;
        $dailyLimit = 50000;
        
        if ($newTotal >= ($dailyLimit * 0.8)) {
            $payload['reasons'][] = 'Reaching 80% of daily limit';
        }
        
        return $next($payload);
    }
}
