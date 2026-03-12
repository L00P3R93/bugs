<?php

namespace Database\Seeders;

use App\Models\Severity;
use Illuminate\Database\Seeder;

class SeveritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $severities = [
            [
                'name' => 'P0 - Critical',
                'multiplier' => 3.00,
                'definition' => 'System down, security breach, data loss. Example: Production database exposed',
                'is_active' => true,
            ],
            [
                'name' => 'P1 - High',
                'multiplier' => 2.00,
                'definition' => 'Major feature broken, significant security risk. Example: Payment processing fails',
                'is_active' => true,
            ],
            [
                'name' => 'P2 - Medium',
                'multiplier' => 1.00,
                'definition' => 'Feature partially broken, workaround exists. Example: Button doesn\'t work on mobile',
                'is_active' => true,
            ],
            [
                'name' => 'P3 - Low',
                'multiplier' => 0.50,
                'definition' => 'Minor inconvenience, cosmetic. Example: Misaligned icon',
                'is_active' => true,
            ],
        ];

        $this->command->warn(PHP_EOL.'Creating Severities...');
        foreach ($severities as $severityData) {
            Severity::query()->create($severityData);
        }
        $this->command->info(count($severities).' Severities seeded successfully');
    }
}
