<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\LogsActivityAllDirty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, LogsActivityAllDirty;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'path',
        'disk',
        'mime_type',
        'size',
        'alt',
        'title',
        'caption',
        'sizes',
        'uploaded_by',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'featured_image_id');
    }

    /**
     * Alias for posts() - media featured by posts.
     *
     * @return HasMany<Post, $this>
     */
    public function featuredByPosts(): HasMany
    {
        return $this->posts();
    }

    /**
     * Check if media is used anywhere.
     */
    public function isUsed(): bool
    {
        return $this->usage_count > 0;
    }

    /**
     * Get the usage count for this media.
     */
    public function getUsageCountAttribute(): int
    {
        return $this->posts()->count();
    }

    /**
     * Scope to get only unused media.
     *
     * @param  Builder<Media>  $query
     * @return Builder<Media>
     */
    public function scopeUnused(Builder $query): Builder
    {
        return $query->whereDoesntHave('posts');
    }

    /**
     * Scope to get only images.
     *
     * @param  Builder<Media>  $query
     * @return Builder<Media>
     */
    public function scopeImages(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope to get only documents (non-images).
     *
     * @param  Builder<Media>  $query
     * @return Builder<Media>
     */
    public function scopeDocuments(Builder $query): Builder
    {
        return $query->where('mime_type', 'not like', 'image/%');
    }

    public function getUrl(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the URL accessor for easier access.
     */
    public function getUrlAttribute(): string
    {
        return $this->getUrl();
    }

    public function getSizeUrl(string $size): ?string
    {
        $sizes = $this->sizes;

        if (! $sizes || ! isset($sizes[$size])) {
            return $this->getUrl();
        }

        return Storage::disk($this->disk)->url($sizes[$size]);
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->getSizeUrl('thumbnail');
    }

    public function getMediumUrl(): ?string
    {
        return $this->getSizeUrl('medium');
    }

    public function getLargeUrl(): ?string
    {
        return $this->getSizeUrl('large');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getFormattedSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2).' '.$units[$index];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sizes' => 'array',
            'size' => 'integer',
        ];
    }
}
