<?php

namespace App\Filament\Resources\Bugs\Actions;

use App\Enums\BugStatus;
use App\Models\Bug;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;

class DuplicateBugAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Mark as Duplicate')
            ->color('info')
            ->icon('')
            ->schema([
                Section::make()->schema([
                    Select::make('duplicate_of_id')
                        ->label('Duplicate Of Bug')
                        ->prefixIcon('hugeicons-file-bitcoin')
                        ->prefixIconColor('primary')
                        ->native(false)
                        ->searchable()
                        ->placeholder('Search by bug number or title…')
                        ->getSearchResultsUsing(function (string $search, Bug $record): array {
                            return Bug::query()
                                ->where('id', '!=', $record->id)
                                ->where(function ($query) use ($search): void {
                                    $query->where('bug_no', 'like', "%{$search}%")
                                        ->orWhere('title', 'like', "%{$search}%");
                                })
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (Bug $bug): array => [
                                    $bug->id => "[{$bug->bug_no}] {$bug->title}",
                                ])
                                ->all();
                        })
                        ->getOptionLabelUsing(function (mixed $value): ?string {
                            $bug = Bug::find($value);

                            return $bug ? "[{$bug->bug_no}] {$bug->title}" : null;
                        })
                        ->required(),
                ])->columnSpanFull(),
            ])
            ->requiresConfirmation()
            ->modalHeading('Mark Bug as Duplicate')
            ->modalDescription('Are you sure you want to mark this bug as duplicate? This will update its status to Duplicate.')
            ->modalSubmitActionLabel('Yes, Mark as Duplicate')
            ->visible(fn (?Bug $bug) => $bug && ! $bug->is_paid && in_array($bug->status, [
                BugStatus::UNDER_REVIEW,
                BugStatus::CLOSED,
                BugStatus::REJECTED,
                BugStatus::FIXED,
                BugStatus::SUBMITTED,
            ]))
            ->action(function (Bug $record, array $data): void {
                $record->update([
                    'status' => BugStatus::DUPLICATE,
                    'duplicate_of_id' => $data['duplicate_of_id'],
                ]);

                $originalBug = Bug::find($data['duplicate_of_id']);

                Notification::make()
                    ->title('Bug Marked as Duplicate')
                    ->body("Bug #{$record->bug_no} has been marked as a duplicate of #{$originalBug?->bug_no}.")
                    ->success()
                    ->send();
            });
    }
}
