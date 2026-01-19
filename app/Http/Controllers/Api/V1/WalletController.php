<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Wallet\CreateWalletRequest;
use App\Http\Resources\TransactionResource as ResourcesTransactionResource;
use App\Http\Resources\WalletResource;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WalletController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private WalletService $walletService,
        private TransactionRepositoryInterface $transactionRepository
    ) {
    }

    public function index(Request $request)
    {
        $wallets = $this->walletService->getUserWallets($request->user());

        return response()->json([
            'data' => WalletResource::collection($wallets),
        ]);
    }

    public function store(CreateWalletRequest $request)
    {
        $wallet = $this->walletService->createWallet(
            $request->user(),
            $request->currency
        );

        return response()->json([
            'message' => 'Wallet created successfully',
            'data' => new WalletResource($wallet),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $wallet = $this->walletService->getWallet($id);

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        if (!Gate::allows('view', $wallet)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        return response()->json([
            'data' => new WalletResource($wallet),
        ]);
    }

    public function balance(Request $request, int $id)
    {
        $wallet = $this->walletService->getWallet($id);

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        if (!Gate::allows('view', $wallet)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json([
            'data' => [
                'wallet_id' => $wallet->id,
                'currency' => $wallet->currency,
                'balance' => $wallet->balance,
                'is_blocked' => $wallet->is_blocked,
            ],
        ]);
    }

    public function transactions(Request $request, int $id)
    {
        $wallet = $this->walletService->getWallet($id);

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        if (!Gate::allows('view', $wallet)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $transactions = $this->transactionRepository->getWalletTransactions($id);

        return response()->json([
            'data' => ResourcesTransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
            ],
        ]);
    }
}
