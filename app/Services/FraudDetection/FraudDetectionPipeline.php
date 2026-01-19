<?php

namespace App\Services\FraudDetection;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Pipeline;

class FraudDetectionPipeline
{
    protected array $rules = [
        Rules\MultipleTransfersRule::class,
        Rules\DailyLimitWarningRule::class,
        Rules\LateNightTransactionRule::class,
        Rules\NewAccountHighAmountRule::class,
        Rules\SameIPDifferentAccountsRule::class,
    ];

    public function check(array $transactionData, User $user): array
    {
        return Pipeline::send([
            'data' => $transactionData,
            'user' => $user,
            'flagged' => false,
            'reasons' => [],
        ])->through($this->rules)
          ->thenReturn();
    }
}
