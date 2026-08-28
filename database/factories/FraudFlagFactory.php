<?php

namespace Database\Factories;

use App\Models\Bug;
use App\Models\FraudFlag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FraudFlag>
 */
class FraudFlagFactory extends Factory
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
            'bug_id' => Bug::factory(),
            'flag_type' => fake()->randomElement(['duplicate_pattern', 'suspicious_ip', 'rate_limit', 'suspicious_amount']),
            'confidence_score' => fake()->randomFloat(2, 0, 1),
            'detected_by' => fake()->randomElement(['system', 'manual', 'ml_model']),
            'status' => 'open',
            'resolved_by' => null,
            'resolution_notes' => null,
            'resolved_at' => null,
        ];
    }
}
