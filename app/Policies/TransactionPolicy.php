<?php

namespace App\Policies;

use App\Models\{User, Transaction};

class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->wallet->user_id 
            || $user->id === $transaction->from_user_id 
            || $user->id === $transaction->to_user_id
            || $user->isAdmin();
    }
}
