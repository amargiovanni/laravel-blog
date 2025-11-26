<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactMessageResource\Pages;

use App\Filament\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reply')
                ->label('Reply')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('info')
                ->url(fn (): string => 'mailto:'.$this->record->email.'?subject=Re: '.urlencode($this->record->subject)),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Auto-mark as read when viewing
        /** @var ContactMessage $record */
        $record = $this->record;
        if (! $record->isRead()) {
            $record->markAsRead();
        }

        return $data;
    }
}
