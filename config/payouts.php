<?php

return [

    /*
     * Feature Toggles
     * *************************************************************************************************************
     * Each toggle independently controls one part of ProcessDailyPayouts.
     * Flip any of these in .env without touching code.
     */
    'features' => [

        /*
         * Game Earnings
         * Whether testers are credited for games played, per game type.
         */
        'game_earnings' => [
            '2_players' => env('PAYOUTS_2P_GAMES_EARNINGS_ENABLED', true),
            '3_players' => env('PAYOUTS_3P_GAMES_EARNINGS_ENABLED', true),
            '4_players' => env('PAYOUTS_4P_GAMES_EARNINGS_ENABLED', true),
        ],

        /*
         * Tournament & Jackpot Earnings
         */
        'tournament_earnings_enabled' => env('PAYOUTS_TOURNAMENT_EARNINGS_ENABLED', true),
        'jackpot_earnings_enabled' => env('PAYOUTS_JACKPOT_EARNINGS_ENABLED', true),

        /*
         * Daily Target Tracking
         * Whether reaching a target flips its "*_target_reached" flag (unlocks withdrawals).
         */
        'target_tracking' => [
            '2_players' => env('PAYOUTS_2P_TARGET_TRACKING_ENABLED', true),
            '3_players' => env('PAYOUTS_3P_TARGET_TRACKING_ENABLED', true),
            '4_players' => env('PAYOUTS_4P_TARGET_TRACKING_ENABLED', true),
            'tournament' => env('PAYOUTS_TOURNAMENT_TARGET_TRACKING_ENABLED', true),
            'jackpot' => env('PAYOUTS_JACKPOT_TARGET_TRACKING_ENABLED', true),
        ],

        /*
         * Zero-Balance Reset
         * Whether a tester's balance is reset to zero at midnight if no daily target was met.
         */
        'zero_balance_reset_enabled' => env('PAYOUTS_ZERO_BALANCE_RESET_ENABLED', false),
    ],

];
