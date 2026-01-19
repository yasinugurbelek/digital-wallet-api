<?php

namespace App\Services\FraudDetection\Rules;

use App\Models\Transaction;
use Closure;

class SameIPDifferentAccountsRule
{
    public function handle(array $payload, Closure $next)
    {
        $data = $payload['data'];
        
        if (!empty($data['ip_address'])) {
            $differentAccounts = Transaction::where('ip_address', $data['ip_address'])
                ->where('from_user_id', '!=', $payload['user']->id)
                ->whereDate('created_at', today())
                ->distinct('from_user_id')
                ->count('from_user_id');
            
            if ($differentAccounts >= 2) {
                $payload['reasons'][] = 'Same IP with different accounts';
            }
        }
        
        return $next($payload);
    }
}
