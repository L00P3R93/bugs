<?php

namespace App\Concerns;

use App\Models\User;
use Closure;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'phone' => $this->phoneRules($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate phone numbers.
     * Accepts Kenyan numbers starting with 0 or 254, followed by 9 digits.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string|Closure>
     */
    protected function phoneRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'regex:/^(0|254)\d{9}$/',
            function (string $attribute, mixed $value, Closure $fail) use ($userId): void {
                $normalized = str_starts_with($value, '0')
                    ? '254'.substr($value, 1)
                    : $value;

                $query = User::query()->where('phone', $normalized);

                if ($userId !== null) {
                    $query->where('id', '!=', $userId);
                }

                if ($query->exists()) {
                    $fail('The phone number has already been taken.');
                }
            },
        ];
    }
}
