<?php

declare(strict_types=1);

namespace App\Filament\Resources\PageResource\RelationManagers;

use App\Models\Revision;
use App\Services\RevisionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Revision History';

    protected static ?string $icon = 'heroicon-o-clock';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('revision_number')
                    ->label('Revision #')
                    ->disabled(),
                Forms\Components\TextInput::make('title')
                    ->disabled(),
                Forms\Components\TextInput::make('user.name')
                    ->label('Author')
                    ->disabled(),
                Forms\Components\Toggle::make('is_protected')
                    ->label('Protected')
                    ->helperText('Protected revisions cannot be deleted'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('revision_number')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->width(60),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (Revision $record): string => $record->title),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->placeholder('Unknown')
                    ->width(120),
                Tables\Columns\IconColumn::make('is_autosave')
                    ->label('Auto')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-path')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->width(60)
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_protected')
                    ->label('Protected')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('success')
                    ->width(80)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('revision_number', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_autosave')
                    ->label('Type')
                    ->trueLabel('Autosaves only')
                    ->falseLabel('Manual saves only')
                    ->placeholder('All'),
                Tables\Filters\TernaryFilter::make('is_protected')
                    ->label('Protection')
                    ->trueLabel('Protected only')
                    ->falseLabel('Unprotected only')
                    ->placeholder('All'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('createRevision')
                    ->label('Create Revision')
                    ->icon('heroicon-o-plus')
                    ->action(function (): void {
                        $page = $this->getOwnerRecord();
                        $revision = $page->createRevision();

                        Notification::make()
                            ->title('Revision created')
                            ->body("Revision #{$revision->revision_number} has been created.")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (Revision $record): string => "Revision #{$record->revision_number}")
                    ->modalContent(fn (Revision $record) => view('filament.modals.revision-preview', [
                        'revision' => $record,
                    ]))
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Tables\Actions\Action::make('compare')
                    ->label('Compare')
                    ->icon('heroicon-o-arrows-right-left')
                    ->modalHeading(fn (Revision $record): string => "Compare Revision #{$record->revision_number} with Current")
                    ->modalContent(function (Revision $record) {
                        $revisionService = app(RevisionService::class);
                        $diff = $revisionService->getDiffFromCurrent($this->getOwnerRecord(), $record);

                        return view('filament.modals.revision-diff', [
                            'revision' => $record,
                            'diff' => $diff,
                            'current' => $this->getOwnerRecord(),
                        ]);
                    })
                    ->modalWidth('7xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Revision $record): string => "Restore Revision #{$record->revision_number}")
                    ->modalDescription('This will create a new revision with the current content, then restore the page to this revision. Are you sure?')
                    ->action(function (Revision $record): void {
                        $page = $this->getOwnerRecord();

                        // Create a revision of the current state first
                        $page->createRevision();

                        // Restore to the selected revision
                        $page->restoreToRevision($record);

                        Notification::make()
                            ->title('Revision restored')
                            ->body("Page has been restored to revision #{$record->revision_number}.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('toggleProtection')
                    ->label(fn (Revision $record): string => $record->is_protected ? 'Unprotect' : 'Protect')
                    ->icon(fn (Revision $record): string => $record->is_protected ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->action(function (Revision $record): void {
                        $record->update(['is_protected' => ! $record->is_protected]);

                        Notification::make()
                            ->title($record->is_protected ? 'Revision protected' : 'Revision unprotected')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Revision $record): bool => ! $record->is_protected),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records): void {
                            $deleted = 0;
                            foreach ($records as $record) {
                                if (! $record->is_protected) {
                                    $record->delete();
                                    $deleted++;
                                }
                            }

                            Notification::make()
                                ->title("Deleted {$deleted} revision(s)")
                                ->body('Protected revisions were skipped.')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
