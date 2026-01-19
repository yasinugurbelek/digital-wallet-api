<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
    public function find(int $id): ?Transaction;
    public function create(array $data): Transaction;
    public function getWalletTransactions(int $walletId): LengthAwarePaginator;
    public function getUserTransactions(int $userId): LengthAwarePaginator;
    public function getAllTransactions(): LengthAwarePaginator;
    public function getPendingReview(): LengthAwarePaginator;
    public function updateStatus(int $id, string $status): bool;
}
