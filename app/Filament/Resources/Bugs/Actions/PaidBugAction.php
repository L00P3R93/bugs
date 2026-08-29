<?php

namespace App\Filament\Resources\Bugs\Actions;

use App\Enums\BugStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Bug;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class PaidBugAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Mark as Paid')
            ->color('success')
            ->icon('')
            ->requiresConfirmation()
            ->modalHeading('Mark Bug as Paid')
            ->modalDescription(fn (Bug $bug): string => "Are you sure you want to mark Bug #{$bug->bug_no} as paid? A payout transaction of Ksh. {$bug->final_amount} will be created for {$bug->reporter->name} and the bug will be marked as paid.")
            ->modalSubmitActionLabel('Yes, Mark as Paid')
            ->visible(fn (?Bug $bug) => $bug && ! $bug->is_paid && in_array($bug->status, [
                BugStatus::UNDER_REVIEW,
                BugStatus::CLOSED,
                BugStatus::REJECTED,
                BugStatus::FIXED,
                BugStatus::SUBMITTED,
            ]))
            ->action(function (Bug $record): void {
                $wallet = $record->reporter->wallet;

                DB::transaction(function () use ($record, $wallet): void {
                    $amount = (float) $record->final_amount;

                    Transaction::query()->create([
                        'transaction_no' => 'TRS'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
                        'wallet_id' => $wallet->id,
                        'user_id' => $record->reporter_id,
                        'bug_id' => $record->id,
                        'amount' => $amount,
                        'fee_amount' => 0,
                        'net_amount' => $amount,
                        'currency' => 'KES',
                        'exchange_rate' => 1,
                        'type' => TransactionType::PAYOUT,
                        'status' => TransactionStatus::COMPLETED,
                        'completed_at' => now(),
                    ]);

                    $wallet->increment('balance', $amount);
                    $wallet->increment('available_balance', $amount);
                    $wallet->increment('total_earned', $amount);

                    $record->update([
                        'status' => BugStatus::PAID,
                        'is_paid' => true,
                        'paid_at' => now(),
                        'duplicate_of_id' => null,
                    ]);
                });

                Notification::make()
                    ->title('Bug Marked as Paid')
                    ->body("Bug #{$record->bug_no} has been marked as paid.")
                    ->success()
                    ->send();
            });
    }
}
