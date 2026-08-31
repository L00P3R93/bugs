<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->boolean('daily_2p_games_target_reached')->default(false)->after('daily_target_reached');
            $table->boolean('daily_3p_games_target_reached')->default(false)->after('daily_2p_games_target_reached');
            $table->boolean('daily_4p_games_target_reached')->default(false)->after('daily_3p_games_target_reached');
            $table->boolean('daily_tournament_target_reached')->default(false)->after('daily_4p_games_target_reached');
            $table->boolean('daily_jackpot_target_reached')->default(false)->after('daily_tournament_target_reached');
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn([
                'daily_2p_games_target_reached',
                'daily_3p_games_target_reached',
                'daily_4p_games_target_reached',
                'daily_tournament_target_reached',
                'daily_jackpot_target_reached',
            ]);
        });
    }
};
