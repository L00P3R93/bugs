<?php

namespace App\Filament\Resources\Bugs\Schemas;

use App\Enums\BugStatus;
use App\Models\Bug;
use App\Models\Category;
use App\Models\Severity;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

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
                    RichEditor::make('description')
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
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
                    RichEditor::make('environment')
                        ->columnSpanFull(),
                    RichEditor::make('steps_to_reproduce')
                        ->columnSpanFull(),
                    RichEditor::make('expected_result')
                        ->columnSpanFull(),
                    RichEditor::make('actual_result')
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
                        ->options(BugStatus::class)
                        ->default('submitted')
                        ->native(false)
                        ->searchable()
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
