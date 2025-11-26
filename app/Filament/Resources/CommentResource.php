<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Comment Details')
                    ->schema([
                        Forms\Components\Select::make('post_id')
                            ->relationship('post', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->options([
                                Comment::STATUS_PENDING => 'Pending',
                                Comment::STATUS_APPROVED => 'Approved',
                                Comment::STATUS_REJECTED => 'Rejected',
                                Comment::STATUS_SPAM => 'Spam',
                            ])
                            ->required()
                            ->default(Comment::STATUS_PENDING),
                        Forms\Components\TextInput::make('author_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('author_email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->maxLength(config('comments.max_length', 2000))
                            ->columnSpanFull()
                            ->rows(5),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->disabled(),
                        Forms\Components\TextInput::make('user_agent')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => Comment::STATUS_PENDING,
                        'success' => Comment::STATUS_APPROVED,
                        'danger' => Comment::STATUS_REJECTED,
                        'gray' => Comment::STATUS_SPAM,
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('author_name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('content')
                    ->label('Comment')
                    ->limit(50)
                    ->searchable()
                    ->tooltip(fn (Comment $record): string => Str::limit($record->content, 200)),
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->limit(30)
                    ->sortable()
                    ->url(fn (Comment $record): ?string => $record->post
                        ? route('filament.admin.resources.posts.edit', $record->post)
                        : null),
                Tables\Columns\IconColumn::make('parent_id')
                    ->label('Reply')
                    ->boolean()
                    ->getStateUsing(fn (Comment $record): bool => $record->parent_id !== null)
                    ->trueIcon('heroicon-o-arrow-uturn-left')
                    ->falseIcon('')
                    ->trueColor('info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Comment::STATUS_PENDING => 'Pending',
                        Comment::STATUS_APPROVED => 'Approved',
                        Comment::STATUS_REJECTED => 'Rejected',
                        Comment::STATUS_SPAM => 'Spam',
                    ]),
                Tables\Filters\SelectFilter::make('post_id')
                    ->relationship('post', 'title')
                    ->label('Post')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_reply')
                    ->label('Type')
                    ->placeholder('All Comments')
                    ->trueLabel('Replies Only')
                    ->falseLabel('Root Comments Only')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('parent_id'),
                        false: fn (Builder $query) => $query->whereNull('parent_id'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Comment $record): bool => $record->status !== Comment::STATUS_APPROVED)
                    ->action(function (Comment $record): void {
                        $record->approve();
                        Notification::make()
                            ->title(__('Comment approved'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Comment $record): bool => $record->status !== Comment::STATUS_REJECTED)
                    ->action(function (Comment $record): void {
                        $record->reject();
                        Notification::make()
                            ->title(__('Comment rejected'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('spam')
                    ->label('Spam')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('gray')
                    ->visible(fn (Comment $record): bool => $record->status !== Comment::STATUS_SPAM)
                    ->action(function (Comment $record): void {
                        $record->markAsSpam();
                        Notification::make()
                            ->title(__('Comment marked as spam'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->approve())
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    BulkAction::make('reject')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn (Collection $records) => $records->each->reject())
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    BulkAction::make('spam')
                        ->label('Mark as Spam')
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('gray')
                        ->action(fn (Collection $records) => $records->each->markAsSpam())
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListComments::route('/'),
            'view' => Pages\ViewComment::route('/{record}'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
