<?php

namespace App\Listeners;

use App\Events\SuspiciousActivityDetected;
use App\Models\FraudLog;
use Illuminate\Support\Facades\Log;

class LogSuspiciousActivity
{
    public function handle(SuspiciousActivityDetected $event): void
    {
        FraudLog::create([
            'user_id' => $event->transaction->from_user_id,
            'transaction_id' => $event->transaction->id,
            'rule_type' => 'suspicious_activity',
            'description' => implode(', ', $event->reasons),
            'metadata' => [
                'transaction_id' => $event->transaction->id,
                'amount' => $event->transaction->amount,
                'reasons' => $event->reasons,
            ],
        ]);

        Log::warning('Suspicious activity detected', [
            'user_id' => $event->transaction->from_user_id,
            'transaction_id' => $event->transaction->id,
            'reasons' => $event->reasons,
        ]);
    }
}
