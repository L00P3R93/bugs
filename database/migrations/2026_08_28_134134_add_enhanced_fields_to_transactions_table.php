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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('wallet_id');
            $table->decimal('fee_amount', 10, 2)->default(0)->after('amount');
            $table->decimal('net_amount', 10, 2)->default(0)->after('fee_amount');
            $table->string('currency', 3)->default('KES')->after('net_amount');
            $table->decimal('exchange_rate', 10, 4)->default(1)->after('currency');
            $table->string('payout_method')->nullable()->after('exchange_rate');
            $table->json('payout_details')->nullable()->after('payout_method');
            $table->timestamp('processed_at')->nullable()->after('payout_details');
            $table->timestamp('completed_at')->nullable()->after('processed_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            $table->text('description')->nullable()->after('cancelled_at');
            $table->string('ip_address', 45)->nullable()->after('description');
            $table->text('user_agent')->nullable()->after('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'user_id',
                'fee_amount',
                'net_amount',
                'currency',
                'exchange_rate',
                'payout_method',
                'payout_details',
                'processed_at',
                'completed_at',
                'cancelled_at',
                'description',
                'ip_address',
                'user_agent',
            ]);
        });
    }
};
