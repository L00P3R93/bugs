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
        Schema::create('severities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('multiplier', 10, 2)->default(0.50); // 3.0, 2.0, 1.0, 0.5
            $table->text('definition');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('severities');
    }
};
