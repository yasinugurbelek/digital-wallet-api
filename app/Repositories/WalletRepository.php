<?php

namespace App\Repositories;

use App\Models\Wallet;
use App\Repositories\Contracts\WalletRepositoryInterface;
use Illuminate\Support\Collection;

class WalletRepository implements WalletRepositoryInterface
{
    public function find(int $id): ?Wallet
    {
        return Wallet::with('user')->find($id);
    }

    public function findByUserAndCurrency(int $userId, string $currency): ?Wallet
    {
        return Wallet::where('user_id', $userId)
            ->where('currency', $currency)
            ->first();
    }

    public function getUserWallets(int $userId): Collection
    {
        return Wallet::where('user_id', $userId)->get();
    }

    public function create(array $data): Wallet
    {
        return Wallet::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return Wallet::where('id', $id)->update($data);
    }
}
