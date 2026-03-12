<?php

use App\Models\Bug;
use App\Models\Label;
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
        Schema::create('bug_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Bug::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Label::class)->constrained()->cascadeOnDelete();
            $table->foreignId('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Ensure unique combination of bug and label
            $table->unique(['bug_id', 'label_id'], 'bug_labels_unique');

            // Indexes for performance
            $table->index(['bug_id', 'label_id']);
            $table->index('added_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bug_labels');
    }
};
