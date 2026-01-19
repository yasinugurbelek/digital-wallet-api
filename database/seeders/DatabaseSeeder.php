<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@deneme.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'currency' => 'TRY',
            'balance' => 10000,
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'currency' => 'USD',
            'balance' => 500,
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'currency' => 'EUR',
            'balance' => 300,
        ]);

    
        $user2 = User::create([
            'name' => 'Second User',
            'email' => 'test2@deneme.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        Wallet::create([
            'user_id' => $user2->id,
            'currency' => 'TRY',
            'balance' => 5000,
        ]);

        $this->command->info('Demo users created successfully!');
        $this->command->info('User: test@deneme.com / password');
    }
}
