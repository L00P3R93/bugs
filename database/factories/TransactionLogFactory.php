<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionLog>
 */
class TransactionLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'action' => fake()->randomElement(['created', 'processing', 'completed', 'failed', 'cancelled']),
            'previous_status' => null,
            'new_status' => fake()->randomElement(['pending', 'completed', 'failed']),
            'details' => null,
            'performed_by' => User::factory(),
        ];
    }
}
