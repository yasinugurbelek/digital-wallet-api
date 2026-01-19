<?php

namespace Tests\Feature;

use App\Models\{User, Wallet};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Wallet $wallet;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'currency' => 'TRY',
            'balance' => 10000,
        ]);
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    public function test_successful_deposit()
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/transactions/deposit', [
                'wallet_id' => $this->wallet->id,
                'amount' => 500,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $this->wallet->id,
            'type' => 'deposit',
            'amount' => 500,
        ]);
    }

    public function test_withdrawal_with_insufficient_balance()
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/transactions/withdraw', [
                'wallet_id' => $this->wallet->id,
                'amount' => 20000,
            ]);

        $response->assertStatus(422);
    }

    public function test_successful_transfer()
    {
        $toUser = User::factory()->create();
        $toWallet = Wallet::factory()->create([
            'user_id' => $toUser->id,
            'currency' => 'TRY',
            'balance' => 0,
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/transactions/transfer', [
                'from_wallet_id' => $this->wallet->id,
                'to_wallet_id' => $toWallet->id,
                'amount' => 1000,
            ]);

        $response->assertStatus(201);
    }
}
