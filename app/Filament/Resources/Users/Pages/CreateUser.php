<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\EmailVerificationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    private ?string $plainPassword = null;

    protected function afterCreate(): void
    {
        if ($this->plainPassword && $this->record) {
            $emailVerificationService = app(EmailVerificationService::class);
            /** @var User $user */
            $user = $this->record;
            $emailVerificationService->sendWelcomeEmail($user, $this->plainPassword);
            $emailVerificationService->sendVerificationEmail($user);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store the plain password before hashing
        $this->plainPassword = $data['password'] ?? null;

        // Hash the password if it exists
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Generate username from name if not provided
        if (! isset($data['username']) && isset($data['name'])) {
            $data['username'] = Str::slug($data['name']);
        }

        // Generate account number if not provided
        if (! isset($data['account_no'])) {
            do {
                $accountNo = 'DOB'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            } while (User::where('account_no', $accountNo)->exists());
            $data['account_no'] = $accountNo;
        }

        return $data;
    }
}
