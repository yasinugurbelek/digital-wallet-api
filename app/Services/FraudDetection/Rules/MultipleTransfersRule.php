<?php

namespace App\Services\FraudDetection\Rules;

use App\Models\Transaction;
use Closure;

class MultipleTransfersRule
{
    public function handle(array $payload, Closure $next)
    {
        $user = $payload['user'];
        
        $recentTransfers = Transaction::where('from_user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->distinct('to_user_id')
            ->count('to_user_id');
        
        if ($recentTransfers >= 5) {
            $payload['flagged'] = true;
            $payload['reasons'][] = '5+ transfers to different users within 1 hour';
        }
        
        return $next($payload);
    }
}
