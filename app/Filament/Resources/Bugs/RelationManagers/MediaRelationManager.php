<?php

namespace App\Filament\Resources\Bugs\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('bug_attachment')
                    ->multiple()
                    ->disk('public')
                    ->collection('attachments')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable()
                    ->maxSize(102400)
                    ->acceptedFileTypes(['image/*', 'video/mp4', 'video/mov', 'video/avi', 'video/wmv'])
                    ->helperText('File size cannot exceed 100MB. Only images (JPEG, PNG, GIF) and videos (MP4, MOV, AVI, WMV) are allowed. Videos can be large, please compress if possible.')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_name')
            ->columns([
                TextColumn::make('mime_type')
                    ->label('File Type')
                    ->formatStateUsing(fn ($record) => match (true) {
                        str_starts_with($record->mime_type, 'image/') => '🖼️ Image',
                        str_starts_with($record->mime_type, 'video/') => '📄 Video',
                        default => '📁 File',
                    })
                    ->tooltip(fn ($record) => $record->mime_type),
                TextColumn::make('file_name')
                    ->label('File Name'),
                TextColumn::make('size')
                    ->formatStateUsing(fn ($record) => number_format($record->size / 1024, 1).' KB'),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->icon('hugeicons-file-upload')->label('Upload Attachment')->color('teal'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->icon('hugeicons-file-view')->iconButton()->color('blue')
                    ->url(fn ($record) => $record->getUrl())
                    ->openUrlInNewTab()
                    ->tooltip('View'),
                DeleteAction::make()->icon('hugeicons-delete-04')->iconButton()->color('red')->tooltip('Delete'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
