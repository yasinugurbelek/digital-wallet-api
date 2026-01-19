<?php

namespace App\Repositories\Contracts;

use App\Models\Wallet;
use Illuminate\Support\Collection;

interface WalletRepositoryInterface
{
    public function find(int $id): ?Wallet;
    public function findByUserAndCurrency(int $userId, string $currency): ?Wallet;
    public function getUserWallets(int $userId): Collection;
    public function create(array $data): Wallet;
    public function update(int $id, array $data): bool;
}
