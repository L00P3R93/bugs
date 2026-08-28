<?php

namespace Database\Seeders;

use App\Models\User;
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
        $this->command->warn(PHP_EOL.'Creating Admin User(s)...');
        //        $name = config('app.admin_name');
        //        $phone = config('app.admin_phone');
        //        $email = config('app.admin_email');
        //        $password = config('app.admin_password');

        $admins = [
            0 => [
                'name' => config('app.admin_name'),
                'phone' => config('app.admin_phone'),
                'email' => config('app.admin_email'),
                'password' => config('app.admin_password'),
            ],
            1 => [
                'name' => 'Kennedy Mulwa',
                'phone' => '0724574375',
                'email' => 'kennedymutia101@gmail.com',
                'password' => 'asdfasdf',
            ],
            2 => [
                'name' => 'Zack Njai',
                'phone' => '0790417280',
                'email' => 'zacknjai@gmail.com',
                'password' => 'asdfasdf',
            ],
        ];

        foreach ($admins as $key => $admin) {
            $admin = User::query()->create([
                'account_no' => 'ACC-'.strtoupper(uniqid()),
                'name' => $name = $admin['name'],
                'username' => Str::slug($name),
                'email' => $admin['email'],
                'phone' => $admin['phone'],
                'email_verified_at' => now(),
                'password' => Hash::make($admin['password']),
                'remember_token' => Str::random(10),
                'status' => 'active',
            ]);
            $admin->assignRole('Super Admin');
            $this->command->info("✓ User {$name} created and assigned to Super Admin role.");
        }

        // Create Non-Admin Users
        /*$this->command->warn(PHP_EOL.'Creating Non-Admin Users with Profiles...');
        $users = $this->withProgressBar(20, fn () => collect([User::factory()->create()]));
        $users->each(function (User $user) {
            $roles = ['Admin', 'Tester'];
            $user->assignRole($roles[array_rand($roles)]);
        });
        $this->command->info('✓ Other users '.$users->count().' created and assigned roles.');*/
    }
}
