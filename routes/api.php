<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{AuthController, WalletController, TransactionController};
use App\Http\Controllers\Api\V1\Admin\{AdminTransactionController, AdminWalletController};

Route::prefix('v1')->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    })->middleware('throttle:5,1');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        
        // Wallet routes
        Route::prefix('wallets')->group(function () {
            Route::get('/', [WalletController::class, 'index']);
            Route::post('/', [WalletController::class, 'store']);
            Route::get('{id}', [WalletController::class, 'show']);
            Route::get('{id}/balance', [WalletController::class, 'balance']);
            Route::get('{id}/transactions', [WalletController::class, 'transactions']);
        })->middleware('throttle:10,1');

        // Transaction routes
        Route::prefix('transactions')->group(function () {
            Route::post('deposit', [TransactionController::class, 'deposit']);
            Route::post('withdraw', [TransactionController::class, 'withdraw']);
            Route::post('transfer', [TransactionController::class, 'transfer']);
            Route::get('{id}', [TransactionController::class, 'show']);
        })->middleware('throttle:10,1');

        // Admin routes
        Route::prefix('admin')->middleware(['admin'])->group(function () {
            
            Route::prefix('transactions')->group(function () {
                Route::get('/', [AdminTransactionController::class, 'index']);
                Route::get('pending-review', [AdminTransactionController::class, 'pendingReview']);
                Route::post('{id}/approve', [AdminTransactionController::class, 'approve']);
                Route::post('{id}/reject', [AdminTransactionController::class, 'reject']);
            });

            Route::prefix('wallets')->group(function () {
                Route::post('{id}/block', [AdminWalletController::class, 'block']);
                Route::post('{id}/unblock', [AdminWalletController::class, 'unblock']);
            });
        });
    });
})->middleware('throttle:60,1');
