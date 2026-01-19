<?php

namespace App\Services;

use App\Models\{Transaction, Wallet, User};
use App\Repositories\Contracts\{TransactionRepositoryInterface, WalletRepositoryInterface};
use App\Services\FeeCalculator\FeeCalculatorFactory;
use App\Services\FraudDetection\FraudDetectionPipeline;
use App\Events\{TransactionCreated, SuspiciousActivityDetected};
use App\Enums\Currency;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private WalletRepositoryInterface $walletRepository,
        private FraudDetectionPipeline $fraudDetection
    ) {}


    public function getTransaction(int $id): ?Transaction
    {
        return Transaction::with(['wallet.user', 'fromUser', 'toUser'])
            ->find($id);
    }

    public function deposit(Wallet $wallet, float $amount, ?string $description = null): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount, $description) {
            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            $wallet->update(['balance' => $balanceAfter]);

            $transaction = $this->transactionRepository->create([
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
                'fee' => 0,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'completed',
                'currency' => $wallet->currency,
                'description' => $description,
            ]);

            event(new TransactionCreated($transaction));

            return $transaction;
        });
    }

    public function withdraw(Wallet $wallet, float $amount, ?string $description = null): Transaction
    {
        if ($wallet->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        if ($wallet->isBlocked()) {
            throw new \Exception('Wallet is blocked');
        }

        return DB::transaction(function () use ($wallet, $amount, $description) {
            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore - $amount;

            $wallet->update(['balance' => $balanceAfter]);

            $transaction = $this->transactionRepository->create([
                'wallet_id' => $wallet->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'fee' => 0,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'completed',
                'currency' => $wallet->currency,
                'description' => $description,
            ]);

            event(new TransactionCreated($transaction));

            return $transaction;
        });
    }

    public function transfer(
        Wallet $fromWallet,
        Wallet $toWallet,
        float $amount,
        ?string $idempotencyKey = null,
        ?string $ipAddress = null,
        ?string $description = null
    ): Transaction {
        // Idempotency check
        if ($idempotencyKey) {
            $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        if ($fromWallet->currency !== $toWallet->currency) {
            throw new \Exception('Currency mismatch');
        }

        if ($fromWallet->isBlocked()) {
            throw new \Exception('Sender wallet is blocked');
        }

        // Fraud detection
        $fraudCheck = $this->fraudDetection->check([
            'amount' => $amount,
            'currency' => $fromWallet->currency,
            'to_user_id' => $toWallet->user_id,
            'ip_address' => $ipAddress,
        ], $fromWallet->user);

        // Calculate fee
        $currency = Currency::from($fromWallet->currency);
        $amountTRY = $currency->toTRY($amount);
        $feeCalculator = FeeCalculatorFactory::make($amountTRY);
        $feeTRY = $feeCalculator->calculate($amountTRY);
        $fee = $fromWallet->currency === 'TRY' ? $feeTRY : round($feeTRY / $currency->toTRY(1), 2);

        $totalAmount = $amount + $fee;

        // Validate balance
        if ($fromWallet->balance < $totalAmount) {
            throw new \Exception('Insufficient balance including fee');
        }

        // Check limits
        $this->validateTransferLimits($fromWallet->user, $amountTRY);

        return DB::transaction(function () use (
            $fromWallet,
            $toWallet,
            $amount,
            $fee,
            $fraudCheck,
            $idempotencyKey,
            $ipAddress,
            $description
        ) {
            $totalAmount = $amount + $fee;
            $status = $fraudCheck['flagged'] ? 'pending_review' : 'completed';

            // Deduct from sender
            $senderBalanceBefore = $fromWallet->balance;
            $senderBalanceAfter = $senderBalanceBefore - $totalAmount;
            $fromWallet->update(['balance' => $senderBalanceAfter]);

            $senderTransaction = $this->transactionRepository->create([
                'wallet_id' => $fromWallet->id,
                'type' => 'transfer',
                'amount' => -$amount,
                'fee' => $fee,
                'balance_before' => $senderBalanceBefore,
                'balance_after' => $senderBalanceAfter,
                'status' => $status,
                'currency' => $fromWallet->currency,
                'from_user_id' => $fromWallet->user_id,
                'to_user_id' => $toWallet->user_id,
                'idempotency_key' => $idempotencyKey,
                'ip_address' => $ipAddress,
                'description' => $description,
            ]);

            // Add to receiver (only if not flagged)
            if (!$fraudCheck['flagged']) {
                $receiverBalanceBefore = $toWallet->balance;
                $receiverBalanceAfter = $receiverBalanceBefore + $amount;
                $toWallet->update(['balance' => $receiverBalanceAfter]);

                $receiverTransaction = $this->transactionRepository->create([
                    'wallet_id' => $toWallet->id,
                    'type' => 'transfer',
                    'amount' => $amount,
                    'fee' => 0,
                    'balance_before' => $receiverBalanceBefore,
                    'balance_after' => $receiverBalanceAfter,
                    'status' => $status,
                    'currency' => $toWallet->currency,
                    'from_user_id' => $fromWallet->user_id,
                    'to_user_id' => $toWallet->user_id,
                    'related_transaction_id' => $senderTransaction->id,
                    'description' => $description,
                ]);

                $senderTransaction->update(['related_transaction_id' => $receiverTransaction->id]);
            }

            event(new TransactionCreated($senderTransaction));

            if ($fraudCheck['flagged']) {
                event(new SuspiciousActivityDetected($senderTransaction, $fraudCheck['reasons']));
            }

            return $senderTransaction;
        });
    }

    private function validateTransferLimits(User $user, float $amountTRY): void
    {
        // Single transaction limit
        if ($amountTRY > 10000) {
            throw new \Exception('Maximum single transaction is 10,000 TRY');
        }

        // Daily limit
        $todayTotal = Transaction::where('from_user_id', $user->id)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('amount');

        if ($todayTotal + $amountTRY > 50000) {
            throw new \Exception('Daily transfer limit (50,000 TRY) exceeded');
        }

        // Hourly limit to same user
        $lastHourCount = Transaction::where('from_user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->where('status', 'completed')
            ->count();

        if ($lastHourCount >= 3) {
            throw new \Exception('Maximum 3 transfers to the same user per hour');
        }
    }

    public function approveTransaction(int $transactionId): Transaction
    {
        return DB::transaction(function () use ($transactionId) {
            $transaction = $this->transactionRepository->find($transactionId);
            
            if ($transaction->status !== 'pending_review') {
                throw new \Exception('Transaction is not pending review');
            }

            // Complete the transfer
            $toWallet = $this->walletRepository->findByUserAndCurrency(
                $transaction->to_user_id,
                $transaction->currency
            );

            $receiverBalanceBefore = $toWallet->balance;
            $receiverBalanceAfter = $receiverBalanceBefore + abs($transaction->amount);
            $toWallet->update(['balance' => $receiverBalanceAfter]);

            $receiverTransaction = $this->transactionRepository->create([
                'wallet_id' => $toWallet->id,
                'type' => 'transfer',
                'amount' => abs($transaction->amount),
                'fee' => 0,
                'balance_before' => $receiverBalanceBefore,
                'balance_after' => $receiverBalanceAfter,
                'status' => 'completed',
                'currency' => $toWallet->currency,
                'from_user_id' => $transaction->from_user_id,
                'to_user_id' => $transaction->to_user_id,
                'related_transaction_id' => $transaction->id,
            ]);

            $transaction->update([
                'status' => 'completed',
                'related_transaction_id' => $receiverTransaction->id,
            ]);

            return $transaction;
        });
    }

    public function rejectTransaction(int $transactionId): Transaction
    {
        return DB::transaction(function () use ($transactionId) {
            $transaction = $this->transactionRepository->find($transactionId);
            
            if ($transaction->status !== 'pending_review') {
                throw new \Exception('Transaction is not pending review');
            }

            // Refund sender
            $wallet = $transaction->wallet;
            $refundAmount = abs($transaction->amount) + $transaction->fee;
            $wallet->update(['balance' => $wallet->balance + $refundAmount]);

            $transaction->update(['status' => 'failed']);

            return $transaction;
        });
    }

}
