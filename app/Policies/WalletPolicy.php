<?php

namespace App\Policies;

use App\Models\{User, Wallet};

class WalletPolicy
{
    public function view(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id || $user->isAdmin();
    }

    public function update(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
    }
}
