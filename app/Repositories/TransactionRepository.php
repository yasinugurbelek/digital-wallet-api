<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function find(int $id): ?Transaction
    {
        return Transaction::with(['wallet.user', 'fromUser', 'toUser'])->find($id);
    }

    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function getWalletTransactions(int $walletId): LengthAwarePaginator
    {
        return Transaction::where('wallet_id', $walletId)
            ->with(['fromUser', 'toUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function getUserTransactions(int $userId): LengthAwarePaginator
    {
        return Transaction::whereHas('wallet', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orWhere('from_user_id', $userId)
            ->orWhere('to_user_id', $userId)
            ->with(['wallet.user', 'fromUser', 'toUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function getAllTransactions(): LengthAwarePaginator
    {
        return Transaction::with(['wallet.user', 'fromUser', 'toUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function getPendingReview(): LengthAwarePaginator
    {
        return Transaction::where('status', 'pending_review')
            ->with(['wallet.user', 'fromUser', 'toUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return Transaction::where('id', $id)->update(['status' => $status]);
    }
}
