<?php

namespace App\Rules;

use App\Models\Transaction;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DailyLimitNotExceeded implements ValidationRule
{
    public function __construct(private int $userId)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $todayTotal = Transaction::where('from_user_id', $this->userId)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('amount');

        if ($todayTotal + $value > 50000) {
            $fail('Daily transfer limit (50,000 TRY) would be exceeded. Used today: ' . $todayTotal . ' TRY');
        }
    }
}
