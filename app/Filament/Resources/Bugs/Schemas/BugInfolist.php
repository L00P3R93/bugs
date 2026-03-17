<?php

namespace App\Filament\Resources\Bugs\Schemas;

use App\Filament\Resources\Bugs\BugResource;
use App\Models\Bug;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class BugInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([

                    Section::make('Bug Details')
                        ->icon('hugeicons-bug-02')
                        ->schema([
                            TextEntry::make('bug_no')
                                ->label('Bug Number')
                                ->icon('hugeicons-left-to-right-list-number')
                                ->iconColor('primary')
                                ->weight(FontWeight::Bold)
                                ->copyable()
                                ->copyMessage('Bug number copied!')
                                ->color('primary'),
                            TextEntry::make('title')
                                ->label('Bug Title')
                                ->icon('hugeicons-file-edit')
                                ->iconColor('primary')
                                ->weight(FontWeight::SemiBold)
                                ->columnSpanFull(),
                            TextEntry::make('description')
                                ->label('Description')
                                ->html()
                                ->columnSpanFull(),
                        ])->columns(2)->columnSpanFull(),

                    Section::make('Technical Report')
                        ->icon('hugeicons-file-script')
                        ->description('Environment, reproduction steps, and observed vs expected behaviour.')
                        ->schema([
                            TextEntry::make('environment')
                                ->label('Environment')
                                ->icon('hugeicons-computer')
                                ->iconColor('gray')
                                ->html()
                                ->placeholder('Not provided.')
                                ->columnSpanFull(),
                            TextEntry::make('steps_to_reproduce')
                                ->label('Steps to Reproduce')
                                ->icon('hugeicons-text-indent-less')
                                ->iconColor('warning')
                                ->html()
                                ->placeholder('Not provided.')
                                ->columnSpanFull(),
                            TextEntry::make('expected_result')
                                ->label('Expected Result')
                                ->icon('hugeicons-checkmark-circle-02')
                                ->iconColor('success')
                                ->html()
                                ->placeholder('Not provided.')
                                ->columnSpanFull(),
                            TextEntry::make('actual_result')
                                ->label('Actual Result')
                                ->icon('hugeicons-cancel-circle')
                                ->iconColor('danger')
                                ->html()
                                ->placeholder('Not provided.')
                                ->columnSpanFull(),
                        ])->collapsible()->columnSpanFull(),

                ])->columnSpan(['lg' => 2]),

                Group::make()->schema([

                    Section::make('Classification')
                        ->icon('hugeicons-sorting-01')
                        ->schema([
                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->columnSpanFull(),
                            TextEntry::make('category.name')
                                ->label('Category')
                                ->icon('hugeicons-file-script')
                                ->iconColor('primary'),
                            TextEntry::make('severity.name')
                                ->label('Severity')
                                ->icon('hugeicons-text-indent-less')
                                ->iconColor('warning'),
                            TextEntry::make('reporter.name')
                                ->label('Reported By')
                                ->icon('hugeicons-user-roadside')
                                ->iconColor('primary'),
                            TextEntry::make('duplicateOf.bug_no')
                                ->label('Duplicate Of')
                                ->icon('hugeicons-git-fork')
                                ->iconColor('gray')
                                ->placeholder('—')
                                ->url(function (Bug $record): ?string {
                                    return $record->duplicateOf
                                        ? BugResource::getUrl('view', ['record' => $record->duplicateOf])
                                        : null;
                                })
                                ->openUrlInNewTab(),
                        ])->columns(1),

                    Section::make('Scoring')
                        ->icon('hugeicons-wallet-add-02')
                        ->schema([
                            TextEntry::make('base_amount')
                                ->label('Base Amount')
                                ->icon('hugeicons-money-receive-01')
                                ->iconColor('success')
                                ->prefix('Ksh. ')
                                ->numeric(decimalPlaces: 2),
                            TextEntry::make('final_amount')
                                ->label('Final Amount')
                                ->icon('hugeicons-money-send-01')
                                ->iconColor('success')
                                ->prefix('Ksh. ')
                                ->numeric(decimalPlaces: 2)
                                ->weight(FontWeight::Bold)
                                ->color('success'),
                            IconEntry::make('is_paid')
                                ->label('Awarded')
                                ->boolean()
                                ->trueIcon('hugeicons-checkmark-circle-02')
                                ->falseIcon('hugeicons-cancel-circle')
                                ->trueColor('success')
                                ->falseColor('gray'),
                            TextEntry::make('paid_at')
                                ->label('Awarded At')
                                ->icon('hugeicons-calendar-upload-01')
                                ->iconColor('success')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('Not yet awarded.'),
                        ])->columns(2)->visible(fn () => auth()->user()->isSuperAdmin()),

                    Section::make('Timeline')
                        ->icon('hugeicons-time-02')
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Submitted')
                                ->icon('hugeicons-clock-01')
                                ->iconColor('primary')
                                ->dateTime('d M Y, H:i'),
                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->icon('hugeicons-system-update-01')
                                ->iconColor('gray')
                                ->dateTime('d M Y, H:i'),
                            TextEntry::make('deleted_at')
                                ->label('Deleted At')
                                ->icon('hugeicons-delete-02')
                                ->iconColor('danger')
                                ->dateTime('d M Y, H:i')
                                ->visible(fn (Bug $record): bool => $record->trashed()),
                        ])->columns(1),

                ])->columnSpan(['lg' => 1]),

                Section::make('Activity Log')
                    ->icon('hugeicons-task-01')
                    ->description('A chronological record of all actions taken on this bug.')
                    ->schema([
                        TextEntry::make('remarks')
                            ->hiddenLabel()
                            ->placeholder('No activity recorded yet.')
                            ->html()
                            ->formatStateUsing(function (?string $state): string {
                                if (blank($state)) {
                                    return '';
                                }

                                return collect(explode("\n", $state))
                                    ->filter()
                                    ->map(fn (string $line): string => '<div class="flex items-start gap-2.5 py-2 border-b border-gray-100 dark:border-white/5 last:border-0">
                                        <span class="shrink-0 size-1.5 rounded-full bg-primary-500 mt-2"></span>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">'.$line.'</span>
                                    </div>')
                                    ->implode('');
                            })
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ])
            ->columns(3);
    }
}
