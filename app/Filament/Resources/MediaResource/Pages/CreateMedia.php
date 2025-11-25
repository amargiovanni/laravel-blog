<?php

declare(strict_types=1);

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use App\Services\ImageService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $file = $data['file'];

        // If Filament already stored the file, we need to handle it differently
        if (is_string($file)) {
            // File was already stored by Filament's FileUpload
            $disk = 'public';
            $path = $file;
            $fullPath = Storage::disk($disk)->path($path);

            // Get file info
            $mimeType = Storage::disk($disk)->mimeType($path);
            $size = Storage::disk($disk)->size($path);
            $name = basename($path);

            // Generate sizes for images
            $sizes = [];
            if (str_starts_with($mimeType, 'image/')) {
                $imageService = app(ImageService::class);
                $directory = dirname($path);

                // Read from stored file and generate sizes
                $tempFile = new UploadedFile(
                    $fullPath,
                    $name,
                    $mimeType,
                    null,
                    true
                );

                $result = $imageService->process($tempFile, Auth::id());
                $sizes = $result['sizes'];

                // Delete the original uploaded file since ImageService created a new one
                Storage::disk($disk)->delete($path);
                $path = $result['path'];
            }

            return static::getModel()::create([
                'name' => $name,
                'path' => $path,
                'disk' => $disk,
                'mime_type' => $mimeType,
                'size' => $size,
                'alt' => $data['alt'] ?? null,
                'title' => $data['title'] ?? null,
                'caption' => $data['caption'] ?? null,
                'sizes' => $sizes ?: null,
                'uploaded_by' => Auth::id(),
            ]);
        }

        // Should not reach here normally
        return static::getModel()::create($data);
    }
}
