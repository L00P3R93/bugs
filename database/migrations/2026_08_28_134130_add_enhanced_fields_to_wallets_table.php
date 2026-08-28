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
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('available_balance', 10, 2)->default(0)->after('balance');
            $table->decimal('pending_balance', 10, 2)->default(0)->after('available_balance');
            $table->decimal('total_earned', 10, 2)->default(0)->after('pending_balance');
            $table->decimal('daily_withdrawal_limit', 10, 2)->default(50000)->after('total_earned');
            $table->decimal('monthly_withdrawal_limit', 10, 2)->default(500000)->after('daily_withdrawal_limit');
            $table->boolean('is_locked')->default(false)->after('monthly_withdrawal_limit');
            $table->text('locked_reason')->nullable()->after('is_locked');
            $table->timestamp('locked_at')->nullable()->after('locked_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn([
                'available_balance',
                'pending_balance',
                'total_earned',
                'daily_withdrawal_limit',
                'monthly_withdrawal_limit',
                'is_locked',
                'locked_reason',
                'locked_at',
            ]);
        });
    }
};
