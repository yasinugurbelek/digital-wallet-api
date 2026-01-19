<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\{
    UserRepositoryInterface,
    WalletRepositoryInterface,
    TransactionRepositoryInterface
};
use App\Repositories\{
    UserRepository,
    WalletRepository,
    TransactionRepository
};
use App\Models\{Wallet, Transaction};
use App\Policies\{WalletPolicy, TransactionPolicy};
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Wallet::class => WalletPolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
    }

    public function boot(): void
    {
        // Policy
        Gate::policy(Wallet::class, WalletPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
        
        // Admin gate
        Gate::define('admin', function ($user) {
            return $user->role === 'admin';
        });
    }
}
