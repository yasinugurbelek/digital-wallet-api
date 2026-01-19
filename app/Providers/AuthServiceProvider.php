<?php

namespace App\Providers;

use App\Models\{Wallet, Transaction};
use App\Policies\{WalletPolicy, TransactionPolicy};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Wallet::class => WalletPolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin', function ($user) {
            return $user->role === 'admin';
        });
    }
}
