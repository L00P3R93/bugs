<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Critical Security',
                'description' => 'Authentication bypass, data leaks, injection flaws, remote code execution (RCE)',
                'base_min_amount' => 500.00,
                'base_max_amount' => 5000.00,
                'weight_multiplier' => 5.0,
                'is_featured' => true,
            ],
            [
                'name' => 'Security',
                'description' => 'XSS, CSRF, IDOR, session management issues',
                'base_min_amount' => 100.00,
                'base_max_amount' => 1000.00,
                'weight_multiplier' => 3.0,
                'is_featured' => true,
            ],
            [
                'name' => 'Crash/Data Loss',
                'description' => 'Application crashes, data corruption, unintentional deletion',
                'base_min_amount' => 75.00,
                'base_max_amount' => 500.00,
                'weight_multiplier' => 2.5,
            ],
            [
                'name' => 'Performance',
                'description' => 'Slow load times, memory leaks, optimization opportunities',
                'base_min_amount' => 50.00,
                'base_max_amount' => 300.00,
                'weight_multiplier' => 2.0,
            ],
            [
                'name' => 'Functional',
                'description' => 'Features not working as specified in documentation',
                'base_min_amount' => 25.00,
                'base_max_amount' => 200.00,
                'weight_multiplier' => 1.5,
            ],
            [
                'name' => 'Compatibility',
                'description' => 'Browser, device, or operating system specific issues',
                'base_min_amount' => 15.00,
                'base_max_amount' => 100.00,
                'weight_multiplier' => 1.2,
            ],
            [
                'name' => 'Accessibility',
                'description' => 'WCAG violations, screen reader compatibility issues',
                'base_min_amount' => 20.00,
                'base_max_amount' => 150.00,
                'weight_multiplier' => 1.3,
            ],
            [
                'name' => 'UI/UX',
                'description' => 'Layout issues, typographical errors, visual glitches',
                'base_min_amount' => 10.00,
                'base_max_amount' => 75.00,
                'weight_multiplier' => 1.0,
            ],
        ];

        $this->command->warn(PHP_EOL.'Creating Categories...');

        foreach ($categories as $categoryData) {
            Category::query()->create([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'description' => $categoryData['description'],
                'base_min_amount' => $categoryData['base_min_amount'],
                'base_max_amount' => $categoryData['base_max_amount'],
                'weight_multiplier' => $categoryData['weight_multiplier'],
                'is_active' => true,
                'is_featured' => fake()->randomElement([true, false]),
            ]);
        }
        $this->command->info(count($categories).' Categories seeded successfully');
    }
}
