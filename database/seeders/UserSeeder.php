<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin User
        $this->command->warn(PHP_EOL.'Creating Admin User...');
        $name = config('app.admin_name');
        $phone = config('app.admin_phone');
        $email = config('app.admin_email');
        $password = config('app.admin_password');
        $admin = User::query()->create([
            'account_no' => 'ACC'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'name' => $name,
            'username' => Str::slug($name),
            'email' => $email,
            'phone' => $phone,
            'email_verified_at' => now(),
            'password' => Hash::make($password),
            'remember_token' => Str::random(10),
            'status' => 'active',
        ]);
        $admin->assignRole('Super Admin');
        $this->command->info("✓ User {$name} created and assigned to Super Admin role.");

        // Create Non-Admin Users
        $this->command->warn(PHP_EOL.'Creating Non-Admin Users with Profiles...');
        $users = $this->withProgressBar(20, fn () => collect([User::factory()->create()]));
        $users->each(function (User $user) {
            $roles = ['Admin', 'Tester'];
            $user->assignRole($roles[array_rand($roles)]);
        });
        $this->command->info('✓ Other users '.$users->count().' created and assigned roles.');
    }
}
