<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'description' => 'nullable|string|max:500',
        ];
    }
}
