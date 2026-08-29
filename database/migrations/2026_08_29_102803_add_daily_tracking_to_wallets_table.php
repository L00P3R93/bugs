<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->unsignedInteger('daily_games_played')->default(0)->after('total_earned');
            $table->decimal('daily_earned', 10, 2)->default(0)->after('daily_games_played');
            $table->boolean('daily_target_reached')->default(false)->after('daily_earned');
            $table->timestamp('last_daily_reset_at')->nullable()->after('daily_target_reached');
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn([
                'daily_games_played',
                'daily_earned',
                'daily_target_reached',
                'last_daily_reset_at',
            ]);
        });
    }
};
