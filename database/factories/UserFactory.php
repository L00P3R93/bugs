<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Faker\Providers\KenyaProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Create a new factory instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->faker->addProvider(new KenyaProvider($this->faker));
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_no' => 'ACC'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'name' => $name = $this->faker->name(),
            'username' => Str::slug($name),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->kenyanPhone(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status' => fake()->randomElement(UserStatus::cases()),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
