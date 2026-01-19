<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SufficientBalance;

class WithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                new SufficientBalance($this->wallet_id),
            ],
            'description' => 'nullable|string|max:500',
        ];
    }
}
