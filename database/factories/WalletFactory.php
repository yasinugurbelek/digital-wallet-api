<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'currency' => fake()->randomElement(['TRY', 'USD', 'EUR']),
            'balance' => fake()->randomFloat(2, 100, 10000),
            'is_blocked' => false,
            'block_reason' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Cüzdanın bloke edilmiş durumu için özel bir "state"
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_blocked' => true,
            'block_reason' => 'Güvenlik şüphesi nedeniyle bloke edildi.',
        ]);
    }
}