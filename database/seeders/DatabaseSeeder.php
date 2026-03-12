<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(SeveritySeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(LabelSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(BugSeeder::class);
    }
}
