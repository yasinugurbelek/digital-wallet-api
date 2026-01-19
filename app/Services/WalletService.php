<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\User;
use App\Repositories\Contracts\WalletRepositoryInterface;
use App\Events\WalletBlocked;
use App\Events\WalletUnblocked;

class WalletService
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function createWallet(User $user, string $currency): Wallet
    {
        $existing = $this->walletRepository->findByUserAndCurrency($user->id, $currency);
        
        if ($existing) {
            throw new \Exception("Wallet already exists for this currency");
        }

        return $this->walletRepository->create([
            'user_id' => $user->id,
            'currency' => $currency,
            'balance' => 0,
        ]);
    }

    public function getUserWallets(User $user)
    {
        return $this->walletRepository->getUserWallets($user->id);
    }

    public function getWallet(int $walletId): ?Wallet
    {
        return $this->walletRepository->find($walletId);
    }

    public function blockWallet(int $walletId, string $reason): bool
    {
        $result = $this->walletRepository->update($walletId, [
            'is_blocked' => true,
            'block_reason' => $reason,
        ]);

        if ($result) {
            $wallet = $this->walletRepository->find($walletId);
            event(new WalletBlocked($wallet, $reason));
        }

        return $result;
    }

    public function unblockWallet(int $walletId, string $reason): bool
    {
        $result = $this->walletRepository->update($walletId, [
            'is_blocked' => false,
            'block_reason' => null,
        ]);

        if ($result) {
            $wallet = $this->walletRepository->find($walletId);
            event(new WalletUnblocked($wallet, $reason));
        }

        return $result;
    }
}
