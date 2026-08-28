<?php

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
        Schema::create('fraud_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bug_id')->nullable()->constrained()->nullOnDelete();
            $table->string('flag_type'); // duplicate_pattern, suspicious_ip, rate_limit, suspicious_amount
            $table->decimal('confidence_score', 3, 2)->default(0);
            $table->string('detected_by')->default('system'); // system, manual, ml_model
            $table->string('status')->default('open'); // open, investigating, cleared, confirmed
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            $table->timestamp('resolved_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fraud_flags');
    }
};
