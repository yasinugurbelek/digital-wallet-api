<?php

namespace App\Console\Commands;

use App\Models\{Transaction, Wallet, User};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DailyReconciliation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:daily-reconciliation {--date=today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily wallet reconciliation report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') === 'today' ? today() : $this->option('date');
        
        $this->info("Generating reconciliation report for: {$date}");
        $this->newLine();

        $this->info('TRANSACTION SUMMARY');

        $transactionStats = Transaction::whereDate('created_at', $date)
            ->selectRaw('
                type,
                status,
                COUNT(*) as count,
                SUM(amount) as total_amount,
                SUM(fee) as total_fees
            ')
            ->groupBy('type', 'status')
            ->get();

        $table = [];
        foreach ($transactionStats as $stat) {
            $table[] = [
                'Type' => ucfirst($stat->type),
                'Status' => ucfirst($stat->status),
                'Count' => $stat->count,
                'Total Amount' => number_format($stat->total_amount, 2),
                'Total Fees' => number_format($stat->total_fees, 2),
            ];
        }

        if (count($table) > 0) {
            $this->table(
                ['Type', 'Status', 'Count', 'Total Amount', 'Total Fees'],
                $table
            );
        } else {
            $this->warn('No transactions found for this date.');
        }

        $this->info('WALLET BALANCES BY CURRENCY');

        $walletBalances = Wallet::selectRaw('
                currency,
                COUNT(*) as wallet_count,
                SUM(balance) as total_balance,
                AVG(balance) as avg_balance,
                MIN(balance) as min_balance,
                MAX(balance) as max_balance
            ')
            ->groupBy('currency')
            ->get();

        $balanceTable = [];
        foreach ($walletBalances as $balance) {
            $balanceTable[] = [
                'Currency' => $balance->currency,
                'Wallets' => $balance->wallet_count,
                'Total' => number_format($balance->total_balance, 2),
                'Average' => number_format($balance->avg_balance, 2),
                'Min' => number_format($balance->min_balance, 2),
                'Max' => number_format($balance->max_balance, 2),
            ];
        }

        $this->table(
            ['Currency', 'Wallets', 'Total', 'Average', 'Min', 'Max'],
            $balanceTable
        );

        $this->newLine();

        $this->info('FEE COLLECTION SUMMARY');

        $feeStats = Transaction::whereDate('created_at', $date)
            ->where('fee', '>', 0)
            ->selectRaw('
                currency,
                COUNT(*) as transaction_count,
                SUM(fee) as total_fees,
                AVG(fee) as avg_fee
            ')
            ->groupBy('currency')
            ->get();

        $feeTable = [];
        $totalFeesCollected = 0;

        foreach ($feeStats as $fee) {
            $feeTable[] = [
                'Currency' => $fee->currency,
                'Transactions' => $fee->transaction_count,
                'Total Fees' => number_format($fee->total_fees, 2),
                'Average Fee' => number_format($fee->avg_fee, 2),
            ];
            $totalFeesCollected += $fee->total_fees;
        }

        if (count($feeTable) > 0) {
            $this->table(
                ['Currency', 'Transactions', 'Total Fees', 'Average Fee'],
                $feeTable
            );
            $this->info("💰 Total Fees Collected: " . number_format($totalFeesCollected, 2));
        } else {
            $this->warn('No fees collected on this date.');
        }

        $this->newLine();

        $this->info('TOP 10 ACTIVE USERS');

        $topUsers = DB::table('transactions')
            ->join('users', function($join) {
                $join->on('transactions.from_user_id', '=', 'users.id')
                     ->orOn('transactions.to_user_id', '=', 'users.id');
            })
            ->whereDate('transactions.created_at', $date)
            ->selectRaw('
                users.id,
                users.name,
                users.email,
                COUNT(DISTINCT transactions.id) as transaction_count,
                SUM(ABS(transactions.amount)) as total_volume
            ')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_volume')
            ->limit(10)
            ->get();

        $userTable = [];
        foreach ($topUsers as $user) {
            $userTable[] = [
                'User ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Transactions' => $user->transaction_count,
                'Volume' => number_format($user->total_volume, 2),
            ];
        }

        if (count($userTable) > 0) {
            $this->table(
                ['User ID', 'Name', 'Email', 'Transactions', 'Volume'],
                $userTable
            );
        } else {
            $this->warn('No active users found for this date.');
        }

        $this->newLine();

        $this->info('SYSTEM HEALTH CHECK');
        $failedTransactions = Transaction::whereDate('created_at', $date)
            ->where('status', 'failed')
            ->count();

        $pendingReview = Transaction::whereDate('created_at', $date)
            ->where('status', 'pending_review')
            ->count();

        $blockedWallets = Wallet::where('is_blocked', true)->count();

        $this->info("Failed Transactions: {$failedTransactions}");
        $this->info("Pending Review: {$pendingReview}");
        $this->info("Blocked Wallets: {$blockedWallets}");

        if ($failedTransactions > 0) {
            $this->warn("{$failedTransactions} failed transactions require investigation");
        }

        if ($pendingReview > 0) {
            $this->warn("{$pendingReview} transactions pending manual review");
        }
    
        return true;
    }
}