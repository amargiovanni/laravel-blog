<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use App\Services\ImageService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Upload')
                    ->schema([
                        Forms\Components\FileUpload::make('file')
                            ->label('File')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('media/'.date('Y/m'))
                            ->visibility('public')
                            ->maxSize(config('blog.media.max_upload_size', 10240))
                            ->acceptedFileTypes(config('blog.media.allowed_types', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']))
                            ->required()
                            ->visibleOn('create')
                            ->columnSpanFull(),
                    ])
                    ->visibleOn('create'),

                Forms\Components\Section::make('Preview')
                    ->schema([
                        Forms\Components\Placeholder::make('preview')
                            ->label('')
                            ->content(fn (Media $record): string => $record->isImage()
                                ? '<img src="'.$record->getUrl().'" class="max-h-64 rounded-lg" />'
                                : '<div class="p-4 bg-gray-100 rounded-lg text-center">'.$record->name.'</div>'
                            )
                            ->extraAttributes(['class' => 'prose'])
                            ->columnSpanFull(),
                    ])
                    ->visibleOn(['edit', 'view']),

                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\TextInput::make('alt')
                            ->label('Alt Text')
                            ->maxLength(255)
                            ->helperText('Describe the image for accessibility'),
                        Forms\Components\TextInput::make('title')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('caption')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('File Information')
                    ->schema([
                        Forms\Components\Placeholder::make('name_display')
                            ->label('File Name')
                            ->content(fn (Media $record): string => $record->name),
                        Forms\Components\Placeholder::make('mime_type_display')
                            ->label('Type')
                            ->content(fn (Media $record): string => $record->mime_type),
                        Forms\Components\Placeholder::make('size_display')
                            ->label('Size')
                            ->content(fn (Media $record): string => $record->getFormattedSize()),
                        Forms\Components\Placeholder::make('uploader_display')
                            ->label('Uploaded By')
                            ->content(fn (Media $record): string => $record->uploader?->name ?? 'Unknown'),
                        Forms\Components\Placeholder::make('usage_display')
                            ->label('Usage')
                            ->content(fn (Media $record): string => $record->usage_count.' post(s)'),
                    ])
                    ->columns(3)
                    ->visibleOn(['edit', 'view']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('')
                    ->getStateUsing(fn (Media $record): ?string => $record->isImage() ? $record->getThumbnailUrl() : null)
                    ->width(60)
                    ->height(60)
                    ->defaultImageUrl(fn (Media $record): string => $record->isImage() ? '' : 'https://placehold.co/60x60/e2e8f0/64748b?text=DOC'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => str_starts_with($state, 'image/') ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('formatted_size')
                    ->label('Size')
                    ->getStateUsing(fn (Media $record): string => $record->getFormattedSize())
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('size', $direction)),
                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Used')
                    ->getStateUsing(fn (Media $record): int => $record->usage_count)
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Uploaded By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'image' => 'Images',
                        'document' => 'Documents',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value']) {
                        'image' => $query->images(),
                        'document' => $query->documents(),
                        default => $query,
                    }),
                Tables\Filters\SelectFilter::make('usage')
                    ->label('Usage')
                    ->options([
                        'used' => 'In Use',
                        'unused' => 'Unused',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value']) {
                        'used' => $query->has('posts'),
                        'unused' => $query->unused(),
                        default => $query,
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Media $record): void {
                        if ($record->isUsed()) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot delete media')
                                ->body('This media is being used by '.$record->usage_count.' post(s).')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('delete_unused')
                        ->label('Delete Unused')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->action(function (\Illuminate\Support\Collection $records, ImageService $imageService): void {
                            $deletedCount = 0;
                            $skippedCount = 0;

                            foreach ($records as $media) {
                                if ($media->isUsed()) {
                                    $skippedCount++;

                                    continue;
                                }

                                $imageService->delete($media);
                                $deletedCount++;
                            }

                            if ($deletedCount > 0) {
                                Notification::make()
                                    ->success()
                                    ->title('Media deleted')
                                    ->body("{$deletedCount} file(s) deleted".($skippedCount > 0 ? ", {$skippedCount} skipped (in use)" : ''))
                                    ->send();
                            } else {
                                Notification::make()
                                    ->warning()
                                    ->title('No media deleted')
                                    ->body('All selected media files are in use.')
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'view' => Pages\ViewMedia::route('/{record}'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
