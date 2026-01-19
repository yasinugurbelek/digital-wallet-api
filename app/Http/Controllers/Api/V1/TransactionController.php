<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Transaction\{DepositRequest, WithdrawRequest, TransferRequest};
use App\Http\Resources\TransactionResource;
use App\Services\{TransactionService, WalletService};
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransactionController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct(
        private TransactionService $transactionService,
        private WalletService $walletService
    ) {
    }

    public function deposit(DepositRequest $request)
    {
        $wallet = $this->walletService->getWallet($request->wallet_id);
        
        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        if (!Gate::allows('update', $wallet)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $transaction = $this->transactionService->deposit(
            $wallet,
            $request->amount,
            $request->description
        );

        return response()->json([
            'message' => 'Deposit successful',
            'data' => new TransactionResource($transaction),
        ], 201);
    }

    public function withdraw(WithdrawRequest $request)
    {
        $wallet = $this->walletService->getWallet($request->wallet_id);
        
        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        if (!Gate::allows('update', $wallet)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $transaction = $this->transactionService->withdraw(
                $wallet,
                $request->amount,
                $request->description
            );

            return response()->json([
                'message' => 'Withdrawal successful',
                'data' => new TransactionResource($transaction),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function transfer(TransferRequest $request)
    {
        $fromWallet = $this->walletService->getWallet($request->from_wallet_id);
        $toWallet = $this->walletService->getWallet($request->to_wallet_id);

        if (!$fromWallet || !$toWallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }
    
        if (!Gate::allows('update', $fromWallet)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $transaction = $this->transactionService->transfer(
                $fromWallet,
                $toWallet,
                $request->amount,
                $request->idempotency_key,
                $request->ip(),
                $request->description
            );

            return response()->json([
                'message' => $transaction->status === 'pending_review' 
                    ? 'Transfer pending review' 
                    : 'Transfer successful',
                'data' => new TransactionResource($transaction),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show(Request $request, int $id)
    {
        $transaction = $this->transactionService->getTransaction($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        if (!Gate::allows('view', $transaction)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json([
            'data' => new TransactionResource($transaction),
        ]);
    }
}
