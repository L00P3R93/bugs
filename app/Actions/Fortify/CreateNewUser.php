<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ], [
            'phone.regex' => 'The phone number must start with 0 or 254 and be followed by 9 digits (e.g. 0712345678 or 254712345678).',
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'username' => Str::slug($input['name']),
            'email' => $input['email'],
            'phone' => $input['phone'],
            'password' => $input['password'],
        ]);

        $user->assignRole('Tester');

        // Don't automatically login, let Fortify handle it with proper redirect
        return $user;
    }
}
