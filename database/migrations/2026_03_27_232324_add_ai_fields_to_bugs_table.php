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
        Schema::table('bugs', function (Blueprint $table) {
            $table->unsignedTinyInteger('ai_uses')->default(0)->after('actual_result');
            $table->timestamp('ai_last_used_at')->nullable()->after('ai_uses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropColumn(['ai_uses', 'ai_last_used_at']);
        });
    }
};
