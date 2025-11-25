<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * Process and store an uploaded image.
     *
     * @return array{path: string, sizes: array<string, string>}
     */
    public function process(UploadedFile $file, int $userId): array
    {
        $disk = config('blog.media.disk', 'public');
        $directory = 'media/'.date('Y/m');

        // Store original
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $this->shouldConvertToWebp() ? 'webp' : $file->getClientOriginalExtension();
        $filename = $this->generateFilename($originalName, $extension);

        $image = $this->manager->read($file->getPathname());

        // Encode the image based on settings
        if ($this->shouldConvertToWebp()) {
            $encoded = $image->toWebp(quality: 85);
        } elseif ($this->shouldOptimize()) {
            $encoded = $image->encodeByMediaType(quality: 85);
        } else {
            $encoded = $image->encode();
        }

        $path = "{$directory}/{$filename}";
        Storage::disk($disk)->put($path, (string) $encoded);

        // Generate sizes
        $sizes = $this->generateSizes($file, $directory, $filename, $disk);

        return [
            'path' => $path,
            'sizes' => $sizes,
        ];
    }

    public function createMedia(UploadedFile $file, int $userId): Media
    {
        $result = $this->process($file, $userId);
        $disk = config('blog.media.disk', 'public');

        return Media::create([
            'name' => $file->getClientOriginalName(),
            'path' => $result['path'],
            'disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sizes' => $result['sizes'],
            'uploaded_by' => $userId,
        ]);
    }

    public function delete(Media $media): bool
    {
        $disk = $media->disk;

        // Delete original
        Storage::disk($disk)->delete($media->path);

        // Delete generated sizes
        if ($media->sizes) {
            foreach ($media->sizes as $sizePath) {
                Storage::disk($disk)->delete($sizePath);
            }
        }

        return $media->delete();
    }

    /**
     * @return array<string, string>
     */
    private function generateSizes(UploadedFile $file, string $directory, string $filename, string $disk): array
    {
        $sizes = [];
        $sizeConfigs = config('blog.media.sizes', []);
        $extension = $this->shouldConvertToWebp() ? 'webp' : pathinfo($filename, PATHINFO_EXTENSION);
        $baseName = pathinfo($filename, PATHINFO_FILENAME);

        foreach ($sizeConfigs as $sizeName => $dimensions) {
            $image = $this->manager->read($file->getPathname());

            $image = $image->cover($dimensions['width'], $dimensions['height']);

            // Encode based on settings
            if ($this->shouldConvertToWebp()) {
                $encoded = $image->toWebp(quality: 80);
            } else {
                $encoded = $image->encode();
            }

            $sizeFilename = "{$baseName}_{$sizeName}.{$extension}";
            $sizePath = "{$directory}/{$sizeFilename}";

            Storage::disk($disk)->put($sizePath, (string) $encoded);

            $sizes[$sizeName] = $sizePath;
        }

        return $sizes;
    }

    private function generateFilename(string $originalName, string $extension): string
    {
        $slug = str()->slug($originalName);
        $unique = str()->random(8);

        return "{$slug}-{$unique}.{$extension}";
    }

    private function shouldOptimize(): bool
    {
        return config('blog.media.optimize', true);
    }

    private function shouldConvertToWebp(): bool
    {
        return config('blog.media.convert_to_webp', true);
    }
}
