<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tag Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Tag::generateUniqueSlug($state ?? ''))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(100)
                            ->alphaDash()
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Posts')
                    ->getStateUsing(fn (Tag $record): int => $record->posts_count)
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->withCount('posts')->orderBy('posts_count', $direction)),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('has_posts')
                    ->label('Has Posts')
                    ->queries(
                        true: fn (Builder $query) => $query->has('posts'),
                        false: fn (Builder $query) => $query->doesntHave('posts'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('merge')
                        ->label('Merge Tags')
                        ->icon('heroicon-o-arrows-pointing-in')
                        ->form([
                            Forms\Components\Select::make('target_tag_id')
                                ->label('Merge into')
                                ->options(fn () => Tag::query()->pluck('name', 'id'))
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (\Illuminate\Support\Collection $records, array $data): void {
                            $targetTag = Tag::find($data['target_tag_id']);

                            if (! $targetTag) {
                                Notification::make()
                                    ->danger()
                                    ->title('Target tag not found')
                                    ->send();

                                return;
                            }

                            $mergedCount = 0;
                            foreach ($records as $tag) {
                                if ($tag->id !== $targetTag->id) {
                                    $tag->mergeInto($targetTag);
                                    $mergedCount++;
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title('Tags merged')
                                ->body("{$mergedCount} tag(s) merged into {$targetTag->name}.")
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
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
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }
}
