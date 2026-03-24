<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WithdrawResultController extends Controller
{
    /**
     * Handle the incoming M-Pesa B2C result callback.
     */
    public function __invoke(Request $request): void
    {
        Log::channel('mpesa')->info('B2C Result: ', $request->all());

        try {
            $result = $request->input('Result');

            if (! is_array($result)) {
                Log::channel('mpesa')->error('B2C Result: unexpected payload structure.', $request->all());

                return;
            }

            $withdraw = Withdraw::query()
                ->where('conversation_id', $result['ConversationID'])
                ->with(['transaction', 'wallet'])
                ->firstOrFail();

            if ($result['ResultCode'] == 0) {
                $withdraw->status = TransactionStatus::COMPLETED;
                $withdraw->transaction_ref = $result['TransactionID'] ?? null;
                $withdraw->saveQuietly();

                $withdraw->transaction->update(['status' => TransactionStatus::COMPLETED]);

                Log::channel('mpesa')->info("Withdrawal {$withdraw->id} completed. Ref: {$withdraw->transaction_ref}");
            } else {
                $withdraw->status = TransactionStatus::FAILED;
                $withdraw->failure_reason = $result['ResultDesc'] ?? 'Unknown error';
                $withdraw->saveQuietly();

                $withdraw->transaction->update(['status' => TransactionStatus::FAILED]);

                // Refund wallet balance
                $withdraw->wallet->increment('balance', $withdraw->amount);

                Log::channel('mpesa')->warning("Withdrawal {$withdraw->id} failed: {$withdraw->failure_reason}. Wallet refunded KES {$withdraw->amount}.");
            }
        } catch (\Exception $e) {
            Log::channel('mpesa')->error('B2C Result handling error: '.$e->getMessage());
        }
    }
}
