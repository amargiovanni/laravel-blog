<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // When status is 'published', ensure published_at is set and not in the future
        if ($data['status'] === 'published') {
            if (empty($data['published_at']) || now()->lt($data['published_at'])) {
                $data['published_at'] = now();
            }
        }

        return $data;
    }
}
