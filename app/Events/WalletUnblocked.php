<?php

namespace App\Events;

use App\Models\Wallet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletUnblocked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Wallet $wallet,
        public string $reason
    ) {
    }
}
