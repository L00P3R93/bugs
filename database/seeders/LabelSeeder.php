<?php

namespace Database\Seeders;

use App\Models\Label;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labels = [
            // Technical Labels - Stack
            ['name' => 'frontend', 'type' => 'technical', 'description' => 'Issues related to client-side code'],
            ['name' => 'backend', 'type' => 'technical', 'description' => 'Issues related to server-side code'],
            ['name' => 'api', 'type' => 'technical', 'description' => 'API endpoint issues'],
            ['name' => 'database', 'type' => 'technical', 'description' => 'Database queries, migrations, or performance'],
            ['name' => 'infrastructure', 'type' => 'technical', 'description' => 'Server, deployment, or hosting issues'],
            ['name' => 'mobile', 'type' => 'technical', 'description' => 'Mobile-specific issues'],
            ['name' => 'desktop', 'type' => 'technical', 'description' => 'Desktop-specific issues'],

            // Technical Labels - Platforms/Browsers
            ['name' => 'ios', 'type' => 'technical', 'description' => 'iOS device specific issues'],
            ['name' => 'android', 'type' => 'technical', 'description' => 'Android device specific issues'],
            ['name' => 'chrome', 'type' => 'technical', 'description' => 'Google Chrome browser issues'],
            ['name' => 'firefox', 'type' => 'technical', 'description' => 'Mozilla Firefox browser issues'],
            ['name' => 'safari', 'type' => 'technical', 'description' => 'Apple Safari browser issues'],
            ['name' => 'edge', 'type' => 'technical', 'description' => 'Microsoft Edge browser issues'],

            // Technical Labels - Features
            ['name' => 'authentication', 'type' => 'technical', 'description' => 'Login, signup, or session issues'],
            ['name' => 'payment', 'type' => 'technical', 'description' => 'Payment processing or billing issues'],
            ['name' => 'search', 'type' => 'technical', 'description' => 'Search functionality issues'],
            ['name' => 'notifications', 'type' => 'technical', 'description' => 'Email, push, or in-app notification issues'],
            ['name' => 'upload', 'type' => 'technical', 'description' => 'File upload or attachment issues'],

            // Process Labels
            ['name' => 'needs-info', 'type' => 'process', 'description' => 'Additional information required from reporter'],
            ['name' => 'under-review', 'type' => 'process', 'description' => 'Currently being reviewed by team'],
            ['name' => 'validated', 'type' => 'process', 'description' => 'Confirmed as valid bug'],
            ['name' => 'duplicate', 'type' => 'process', 'description' => 'Already reported in another ticket'],
            ['name' => 'wont-fix', 'type' => 'process', 'description' => 'Issue accepted but not planned for fix'],
            ['name' => 'fixed', 'type' => 'process', 'description' => 'Issue has been resolved'],
            ['name' => 'regression', 'type' => 'process', 'description' => 'Previously fixed issue has returned'],
            ['name' => 'customer-reported', 'type' => 'process', 'description' => 'Reported directly by customer'],
            ['name' => 'automation-detected', 'type' => 'process', 'description' => 'Found by automated testing'],

            // Special Labels
            ['name' => 'good-first-bug', 'type' => 'special', 'description' => 'Ideal for new testers to get started'],
            ['name' => 'bounty-eligible', 'type' => 'special', 'description' => 'Qualifies for bounty payment'],
            ['name' => 'urgent', 'type' => 'special', 'description' => 'Time-sensitive issue requiring immediate attention'],
        ];

        $this->command->warn(PHP_EOL.'Seeding labels...');
        foreach ($labels as $labelData) {
            Label::query()->create([
                'name' => $labelData['name'],
                'slug' => Str::slug($labelData['name']),
                'description' => $labelData['description'],
                'type' => $labelData['type'],
                'is_active' => true,
            ]);
        }
        $this->command->info('Seeded labels');
    }
}
