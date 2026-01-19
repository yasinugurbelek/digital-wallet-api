<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\{SufficientBalance, DailyLimitNotExceeded};

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_wallet_id' => 'required|exists:wallets,id',
            'to_wallet_id' => 'required|exists:wallets,id|different:from_wallet_id',
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:10000',
                new SufficientBalance($this->from_wallet_id),
                new DailyLimitNotExceeded($this->user()->id),
            ],
            'idempotency_key' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ];
    }
}
