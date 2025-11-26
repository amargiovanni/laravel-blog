<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriberResource\Pages;
use App\Models\Subscriber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;

class SubscriberResource extends Resource
{
    protected static ?string $model = Subscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::active()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Subscriber Information')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('name')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Subscription Status')
                    ->schema([
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label('Verified At')
                            ->native(false),
                        Forms\Components\DateTimePicker::make('unsubscribed_at')
                            ->label('Unsubscribed At')
                            ->native(false),
                        Forms\Components\TextInput::make('subscribed_ip')
                            ->label('Subscribed IP')
                            ->disabled(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function (Subscriber $record): string {
                        if ($record->unsubscribed_at !== null) {
                            return 'Unsubscribed';
                        }
                        if ($record->verified_at !== null) {
                            return 'Active';
                        }

                        return 'Pending';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Pending' => 'warning',
                        'Unsubscribed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Verified')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->placeholder('Not verified'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Subscribed')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscribed_ip')
                    ->label('IP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('unsubscribed_at')
                    ->label('Unsubscribed')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending Verification',
                        'unsubscribed' => 'Unsubscribed',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'active' => $query->active(),
                            'pending' => $query->unverified(),
                            'unsubscribed' => $query->unsubscribed(),
                            default => $query,
                        };
                    }),
                Tables\Filters\Filter::make('verified')
                    ->label('Verified Only')
                    ->query(fn (Builder $query): Builder => $query->verified()),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('subscribed_from')
                            ->label('Subscribed From'),
                        Forms\Components\DatePicker::make('subscribed_until')
                            ->label('Subscribed Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['subscribed_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['subscribed_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Subscriber $record): bool => ! $record->isVerified())
                    ->action(fn (Subscriber $record) => $record->markAsVerified()),
                Action::make('unsubscribe')
                    ->label('Unsubscribe')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Subscriber $record): bool => $record->isActive())
                    ->action(fn (Subscriber $record) => $record->markAsUnsubscribed()),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records) {
                            return static::exportToCsv($records);
                        }),
                ]),
            ])
            ->headerActions([
                Action::make('exportAll')
                    ->label('Export All')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $records = Subscriber::active()->get();

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
            'index' => Pages\ListSubscribers::route('/'),
            'create' => Pages\CreateSubscriber::route('/create'),
            'view' => Pages\ViewSubscriber::route('/{record}'),
            'edit' => Pages\EditSubscriber::route('/{record}/edit'),
        ];
    }

    protected static function exportToCsv(Collection $records): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'subscribers-'.now()->format('Y-m-d-His').'.csv';

        return Response::streamDownload(function () use ($records): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Email', 'Name', 'Status', 'Verified At', 'Subscribed At', 'IP Address']);

            foreach ($records as $record) {
                $status = match (true) {
                    $record->unsubscribed_at !== null => 'Unsubscribed',
                    $record->verified_at !== null => 'Active',
                    default => 'Pending',
                };

                fputcsv($handle, [
                    $record->email,
                    $record->name ?? '',
                    $status,
                    $record->verified_at?->format('Y-m-d H:i:s') ?? '',
                    $record->created_at->format('Y-m-d H:i:s'),
                    $record->subscribed_ip ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
