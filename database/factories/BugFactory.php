<?php

namespace Database\Factories;

use App\Enums\BugStatus;
use App\Models\Bug;
use App\Models\Category;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bug>
 */
class BugFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(BugStatus::cases());

        $bugId = $status === BugStatus::DUPLICATE ? Bug::query()->inRandomOrder()->first()?->id : null;
        $user = User::query()->inRandomOrder()->first();
        $category = Category::query()->inRandomOrder()->first();
        $severity = Severity::query()->inRandomOrder()->first();
        $baseAmount = $category->base_min_amount;
        $finalAmount = $baseAmount + ($severity->multiplier * $baseAmount);

        return [
            'reporter_id' => $user->id,
            'category_id' => $category->id,
            'severity_id' => $severity->id,
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(10),
            'environment' => fake()->userAgent(),
            'steps_to_reproduce' => fake()->paragraph(10),
            'expected_result' => fake()->paragraph(5),
            'actual_result' => fake()->paragraph(5),
            'status' => $status,
            'remarks' => fake()->paragraph(5),
            'duplicate_of_id' => $bugId,
            'base_amount' => $baseAmount,
            'final_amount' => $finalAmount,
            'is_paid' => in_array($status, [BugStatus::PAID, BugStatus::FIXED, BugStatus::CLOSED]),
            'paid_at' => in_array($status, [BugStatus::PAID, BugStatus::FIXED, BugStatus::CLOSED]) ? fake()->dateTimeBetween('-1 month', 'now') : null,
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Bug $bug) {
            // Set remarks for seeded bugs
            //            if (app()->runningInConsole()) {
            //                $reporterName = $bug->reporter->name;
            //                $bug->remarks = "Bug [{$bug->bug_no}] submitted by {$reporterName} on ".now()->toDateTimeString();
            //            }
        });
    }
}
