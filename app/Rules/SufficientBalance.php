<?php

namespace App\Rules;

use App\Models\Wallet;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SufficientBalance implements ValidationRule
{
    public function __construct(private ?int $walletId)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->walletId) {
            return;
        }

        $wallet = Wallet::find($this->walletId);

        if (!$wallet) {
            $fail('Wallet not found');
            return;
        }

        if ($wallet->balance < $value) {
            $fail('Insufficient balance. Available: ' . $wallet->balance . ' ' . $wallet->currency);
        }
    }
}
