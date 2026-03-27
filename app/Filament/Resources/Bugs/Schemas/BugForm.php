<?php

namespace App\Filament\Resources\Bugs\Schemas;

use App\Enums\BugStatus;
use App\Models\Bug;
use App\Models\Category;
use App\Models\Severity;
use App\Services\GeminiService;
use Filament\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Request;

class BugForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()->schema([
                Section::make('Bug Details')->schema([
                    TextInput::make('bug_no')
                        ->label('Bug Number')
                        ->prefixIcon(Heroicon::OutlinedHashtag)
                        ->prefixIconColor('primary')
                        ->disabled()
                        ->dehydrated()
                        ->unique(ignoreRecord: true)
                        ->default(fn () => 'BUG'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT))
                        ->required(),
                    TextInput::make('title')
                        ->label('Bug Title')
                        ->prefixIcon('hugeicons-file-bitcoin')
                        ->prefixIconColor('primary')
                        ->required(),
                    MarkdownEditor::make('description')
                        ->placeholder('Describe the bug in your own words first. What happened? What did you expect? Steps to reproduce?')
                        ->helperText('Write your own description first — the more detail you add, the better AI can help refine it.')
                        ->hintIcon('heroicon-o-pencil')
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
                Section::make()
                    ->schema([
                        TextEntry::make('ai_hint')
                            ->label('')
                            ->state('✍️ Write your description above first, then use AI to improve it. You have 2 AI requests on this bug report.'),
                        Actions::make([
                            Action::make('improve_description')
                                ->label('Improve My Description')
                                ->icon('heroicon-o-sparkles')
                                ->color('primary')
                                ->tooltip('AI will refine and improve what you have already written')
                                ->disabled(fn (?Bug $record) => $record
                                    ? $record->ai_uses >= 2
                                    : session()->get('bug_ai_uses_'.session()->getId(), 0) >= 2
                                )
                                ->requiresConfirmation()
                                ->modalHeading('Improve with AI?')
                                ->modalDescription('AI will refine your current description. Your original text will be replaced. This uses 1 of your 2 AI requests.')
                                ->modalSubmitActionLabel('Yes, improve it')
                                ->action(function (Get $get, Set $set, ?Bug $record): void {
                                    $description = $get('description');
                                    $sessionKey = 'bug_ai_uses_'.session()->getId();
                                    $uses = $record ? $record->ai_uses : session()->get($sessionKey, 0);

                                    if (blank($description)) {
                                        Notification::make()
                                            ->title('Nothing to Improve')
                                            ->body('Write something in the description field first, then ask AI to improve it.')
                                            ->warning()
                                            ->send();

                                        return;
                                    }

                                    if ($uses >= 2) {
                                        Notification::make()
                                            ->title('AI Limit Reached')
                                            ->body('You have used both AI requests for this bug report. Edit the description manually.')
                                            ->danger()
                                            ->send();

                                        return;
                                    }

                                    try {
                                        $prompt = sprintf(
                                            'Rewrite this bug description using exactly 500 characters in plain text only. Output a single improved description with no headings, no options, no markdown, no explanations. Keep the original meaning. Do not invent details. Title: %s. Description: %s',
                                            $get('title'),
                                            $description,
                                        );

                                        $improved = app(GeminiService::class)->improveOrGenerate($prompt);

                                        $set('description', $improved);

                                        if ($record) {
                                            $record->increment('ai_uses');
                                            $record->update(['ai_last_used_at' => now()]);
                                        } else {
                                            session()->put($sessionKey, $uses + 1);
                                        }

                                        Notification::make()
                                            ->title('Description Improved')
                                            ->body('AI has refined your description.')
                                            ->success()
                                            ->send();
                                    } catch (\RuntimeException $e) {
                                        Notification::make()
                                            ->title('AI Error')
                                            ->body($e->getMessage())
                                            ->danger()
                                            ->send();
                                    }
                                }),

                            Action::make('generate_from_title')
                                ->label('Generate from Title')
                                ->icon('heroicon-o-bolt')
                                ->color('gray')
                                ->tooltip('Only use this if you have nothing written yet')
                                ->disabled(fn (?Bug $record) => $record
                                    ? $record->ai_uses >= 2
                                    : session()->get('bug_ai_uses_'.session()->getId(), 0) >= 2
                                )
                                ->requiresConfirmation()
                                ->modalHeading('Generate from Title?')
                                ->modalDescription('This will create a draft description from your title only. Best used when the description field is empty. Uses 1 of your 2 AI requests.')
                                ->modalSubmitActionLabel('Generate draft')
                                ->action(function (Get $get, Set $set, ?Bug $record): void {
                                    $title = $get('title');
                                    $description = $get('description');
                                    $sessionKey = 'bug_ai_uses_'.session()->getId();
                                    $uses = $record ? $record->ai_uses : session()->get($sessionKey, 0);

                                    if (blank($title)) {
                                        Notification::make()
                                            ->title('Title Required')
                                            ->body('Add a bug title first so AI has context to work with.')
                                            ->warning()
                                            ->send();

                                        return;
                                    }

                                    if ($uses >= 2) {
                                        Notification::make()
                                            ->title('AI Limit Reached')
                                            ->body('You have used both AI requests for this bug report. Edit the description manually.')
                                            ->danger()
                                            ->send();

                                        return;
                                    }

                                    if (filled($description)) {
                                        Notification::make()
                                            ->title('You Already Have a Description')
                                            ->body('Use "Improve My Description" instead to refine what you have written.')
                                            ->warning()
                                            ->send();

                                        return;
                                    }

                                    try {
                                        $prompt = sprintf(
                                            'Write a single plain-text bug description using exactly 500 characters based only on this title: %s. No headings, no options, no markdown, no explanations.',
                                            $title,
                                        );

                                        $generated = app(GeminiService::class)->improveOrGenerate($prompt);

                                        $set('description', $generated);

                                        if ($record) {
                                            $record->increment('ai_uses');
                                            $record->update(['ai_last_used_at' => now()]);
                                        } else {
                                            session()->put($sessionKey, $uses + 1);
                                        }

                                        Notification::make()
                                            ->title('Draft Generated')
                                            ->body('Fill in the placeholder sections marked in [brackets].')
                                            ->warning()
                                            ->send();
                                    } catch (\RuntimeException $e) {
                                        Notification::make()
                                            ->title('AI Error')
                                            ->body($e->getMessage())
                                            ->danger()
                                            ->send();
                                    }
                                }),
                        ])->columnSpanFull(),

                        TextEntry::make('ai_uses_display')
                            ->label('AI Requests Used')
                            ->state(fn (?Bug $record) => $record
                                ? ($record->ai_uses.' / 2 used'.($record->hasAiUsesRemaining() ? '' : ' — limit reached'))
                                : 'Creating new report — 2 requests available'
                            )
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->compact(),
                Section::make('Upload Attachment')->schema([
                    SpatieMediaLibraryFileUpload::make('bug_attachment')
                        ->multiple()
                        ->disk('public')
                        ->collection('attachments')
                        ->preserveFilenames()
                        ->maxSize(102400)
                        ->acceptedFileTypes(['image/*', 'video/mp4', 'video/mov', 'video/avi', 'video/wmv'])
                        ->helperText('File size cannot exceed 100MB. Only images (JPEG, PNG, GIF) and videos (MP4, MOV, AVI, WMV) are allowed. Videos can be large, please compress if possible.')
                        ->afterStateHydrated(fn (SpatieMediaLibraryFileUpload $component) => $component->state([]))
                        ->columnSpanFull(),
                ])->columnSpanFull(),
                Section::make('Bug Reports')->schema([
                    MarkdownEditor::make('environment')
                        ->default(fn () => Request::userAgent())
                        ->columnSpanFull(),
                    MarkdownEditor::make('steps_to_reproduce')
                        ->columnSpanFull(),
                    MarkdownEditor::make('expected_result')
                        ->columnSpanFull(),
                    MarkdownEditor::make('actual_result')
                        ->columnSpanFull(),
                ])->columnSpanFull()->collapsed(),
            ])->columnSpan(['lg' => 2]),
            Group::make()->schema([
                Section::make('Bug Associations')->schema([
                    Select::make('category_id')
                        ->label('Bug Category')
                        ->relationship('category', 'name')
                        ->prefixIcon('hugeicons-file-script')
                        ->prefixIconColor('primary')
                        ->native(false)
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                            $category = Category::query()->find($state);
                            $baseAmount = $category?->base_min_amount ?? 0;
                            $set('base_amount', $baseAmount);

                            $severity = Severity::query()->find($get('severity_id'));
                            $finalAmount = $severity && $category
                                ? $baseAmount + ($severity->multiplier * $baseAmount)
                                : $baseAmount;
                            $set('final_amount', $finalAmount);
                        })
                        ->required(),
                    Select::make('severity_id')
                        ->label('Bug Severity')
                        ->relationship('severity', 'name')
                        ->prefixIcon('hugeicons-text-indent-less')
                        ->prefixIconColor('primary')
                        ->native(false)
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                            $category = Category::query()->find($get('category_id'));
                            $severity = Severity::query()->find($state);

                            if ($category && $severity) {
                                $baseAmount = $category->base_min_amount;
                                $set('final_amount', $baseAmount + ($severity->multiplier * $baseAmount));
                            }
                        })
                        ->required(),
                    Select::make('status')
                        ->label('Bug Status')
                        ->prefixIcon('hugeicons-status')
                        ->prefixIconColor('primary')
                        ->native(false)
                        ->searchable()
                        ->options(BugStatus::class)
                        ->default('submitted')
                        ->required()
                        ->visible(fn (?Bug $record) => $record !== null && auth()->user()->isSuperAdmin()),
                    Select::make('reporter_id')
                        ->label('Reported By')
                        ->prefixIcon('hugeicons-user-roadside')
                        ->prefixIconColor('primary')
                        ->relationship(
                            name: 'reporter',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query) => $query->latest()->limit(10)
                        )
                        ->native(false)
                        ->searchable()
                        ->required()
                        ->visible(fn (?Bug $record) => $record !== null && auth()->user()->isSuperAdmin()),
                ]),
                Section::make('Bug Amounts')->schema([
                    TextInput::make('base_amount')
                        ->label('Base Amount')
                        ->prefixIcon('hugeicons-money-receive-01')
                        ->prefixIconColor('primary')
                        ->prefix('Ksh.')
                        ->disabled()
                        ->dehydrated()
                        ->numeric()
                        ->default(0.0),
                    TextInput::make('final_amount')
                        ->label('Final Amount')
                        ->prefixIcon('hugeicons-money-send-01')
                        ->prefixIconColor('primary')
                        ->prefix('Ksh.')
                        ->disabled()
                        ->dehydrated()
                        ->numeric()
                        ->default(0.0),
                ])->visible(fn () => auth()->user()->isSuperAdmin()),
            ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }
}
