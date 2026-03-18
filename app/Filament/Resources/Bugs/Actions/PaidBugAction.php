<?php

namespace App\Filament\Resources\Bugs\Actions;

use App\Enums\BugStatus;
use App\Enums\TransactionStatus;
use App\Filament\Resources\Transactions\Schemas\TransactionForm;
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
            ->schema(fn (Bug $bug) => TransactionForm::getFormSchema($bug))
            ->requiresConfirmation()
            ->modalHeading('Mark Bug as Paid')
            ->modalDescription("Complete the transaction details below. The reporter's wallet will be credited and the bug will be marked as paid.")
            ->modalSubmitActionLabel('Yes, Mark as Paid')
            ->visible(fn (?Bug $bug) => $bug && ! $bug->is_paid && in_array($bug->status, [
                BugStatus::UNDER_REVIEW,
                BugStatus::CLOSED,
                BugStatus::REJECTED,
                BugStatus::FIXED,
                BugStatus::SUBMITTED,
            ]))
            ->action(function (Bug $record, array $data): void {
                DB::transaction(function () use ($record, $data): void {
                    $transaction = Transaction::query()->create($data);

                    $transaction->wallet->increment('balance', $transaction->amount);

                    $transaction->status = TransactionStatus::COMPLETED;
                    $transaction->saveQuietly();

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
