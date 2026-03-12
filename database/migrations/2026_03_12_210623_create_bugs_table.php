<?php

use App\Models\Category;
use App\Models\Severity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bugs', function (Blueprint $table) {
            $table->id();
            $table->string('bug_no')->unique()->index();
            $table->foreignId('reporter_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Category::class)->index()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Severity::class)->index()->constrained()->cascadeOnDelete();
            // Content
            $table->string('title');
            $table->text('description');
            $table->text('environment')->nullable();
            $table->text('steps_to_reproduce')->nullable();
            $table->text('expected_result')->nullable();
            $table->text('actual_result')->nullable();
            $table->string('status')->index()->default('submitted');
            // Logs for bug flow
            $table->text('remarks')->nullable(); // Comments will serve as logs, as bug is triaged, reviewed, validated. rejected, closed
            $table->foreignId('duplicate_of_id')->nullable()->constrained('bugs')->nullOnDelete();
            // Scoring
            $table->decimal('base_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            // Payment Status
            $table->boolean('is_paid')->index()->default(false);
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bugs');
    }
};
