<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RedirectResource\Pages;
use App\Models\Post;
use App\Models\Redirect;
use App\Rules\NoRedirectLoop;
use App\Rules\NotSelfRedirect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Redirect Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('source_url')
                            ->label('Source URL')
                            ->placeholder('/old-page')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['regex:/^\//'])
                            ->helperText(__('The URL path to redirect from. Must start with /'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set): void {
                                // Check if source URL matches existing content
                                if ($state) {
                                    $slug = ltrim($state, '/');
                                    if (Post::where('slug', $slug)->exists()) {
                                        Notification::make()
                                            ->title(__('Warning: This URL matches an existing post'))
                                            ->warning()
                                            ->send();
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('target_url')
                            ->label('Target URL')
                            ->placeholder('/new-page')
                            ->required()
                            ->maxLength(255)
                            ->rules([
                                'regex:/^\//i',
                                fn (Get $get): NoRedirectLoop => new NoRedirectLoop(
                                    $get('source_url'),
                                    $form->getRecord()?->id
                                ),
                                fn (Get $get): NotSelfRedirect => new NotSelfRedirect($get('source_url')),
                            ])
                            ->helperText(__('The URL path to redirect to. Must start with /')),
                        Forms\Components\Select::make('status_code')
                            ->label('Redirect Type')
                            ->options([
                                301 => '301 - Permanent Redirect',
                                302 => '302 - Temporary Redirect',
                            ])
                            ->default(301)
                            ->required()
                            ->helperText(__('301 redirects are cached by browsers and search engines')),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText(__('Inactive redirects are not executed')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_active')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('source_url')
                    ->label('From')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('target_url')
                    ->label('To')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\BadgeColumn::make('status_code')
                    ->label('Type')
                    ->colors([
                        'success' => 301,
                        'warning' => 302,
                    ])
                    ->formatStateUsing(fn (int $state): string => $state === 301 ? '301 Permanent' : '302 Temporary'),
                Tables\Columns\IconColumn::make('is_automatic')
                    ->label('Auto')
                    ->boolean()
                    ->trueIcon('heroicon-o-sparkles')
                    ->falseIcon('')
                    ->trueColor('info'),
                Tables\Columns\TextColumn::make('hits')
                    ->label('Hits')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('last_hit_at')
                    ->label('Last Hit')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->placeholder('Never'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_code')
                    ->label('Type')
                    ->options([
                        301 => '301 Permanent',
                        302 => '302 Temporary',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
                Tables\Filters\TernaryFilter::make('is_automatic')
                    ->label('Source')
                    ->trueLabel('Automatic')
                    ->falseLabel('Manual'),
                Tables\Filters\Filter::make('unused')
                    ->label('Unused (no hits in 30 days)')
                    ->query(fn (Builder $query): Builder => $query->where(function ($q): void {
                        $q->whereNull('last_hit_at')
                            ->orWhere('last_hit_at', '<', now()->subDays(30));
                    })),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('toggle')
                    ->label(fn (Redirect $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Redirect $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Redirect $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(fn (Redirect $record) => $record->update(['is_active' => ! $record->is_active]))
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $records = Redirect::all();

                        return static::exportToCsv($records);
                    }),
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
            'index' => Pages\ListRedirects::route('/'),
            'create' => Pages\CreateRedirect::route('/create'),
            'view' => Pages\ViewRedirect::route('/{record}'),
            'edit' => Pages\EditRedirect::route('/{record}/edit'),
        ];
    }

    protected static function exportToCsv(Collection $records): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'redirects-'.now()->format('Y-m-d-His').'.csv';

        return Response::streamDownload(function () use ($records): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Source URL', 'Target URL', 'Status Code', 'Active', 'Automatic', 'Hits', 'Last Hit', 'Created']);

            foreach ($records as $record) {
                fputcsv($handle, [
                    $record->source_url,
                    $record->target_url,
                    $record->status_code,
                    $record->is_active ? 'Yes' : 'No',
                    $record->is_automatic ? 'Yes' : 'No',
                    $record->hits,
                    $record->last_hit_at?->format('Y-m-d H:i:s') ?? '',
                    $record->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
