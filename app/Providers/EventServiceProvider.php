<?php

namespace App\Providers;

use App\Events\{TransactionCreated, SuspiciousActivityDetected, WalletBlocked, WalletUnblocked};
use App\Listeners\LogSuspiciousActivity;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SuspiciousActivityDetected::class => [
            LogSuspiciousActivity::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
