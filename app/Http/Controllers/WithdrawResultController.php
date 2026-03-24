<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WithdrawResultController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Log incoming request
        Log::channel('mpesa')->info('B2C Result: ', $request->all());
        try {
            $withdraw = Withdraw::query()->where('conversation_id', $request->ConversationID)->firstOrFail();
            $withdraw->status = $request->ResultCode == 0 ? TransactionStatus::COMPLETED : TransactionStatus::FAILED;
            $withdraw->transaction_ref = $request->ResultCode == 0 ? $request->TransactionID : null;
            $withdraw->saveQuietly();
        } catch (\Exception $e) {
            Log::error('Withdraw Update Error: '.$e->getMessage());
        }
    }
}
