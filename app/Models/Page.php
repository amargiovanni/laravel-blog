<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\LogsActivityAllDirty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory, LogsActivityAllDirty, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'parent_id',
        'author_id',
        'status',
        'template',
        'published_at',
        'featured_image_id',
        'meta_title',
        'meta_description',
        'focus_keyword',
        'sort_order',
    ];

    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 2;

        $query = static::withTrashed()->where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;

            $query = static::withTrashed()->where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    /**
     * @return BelongsTo<Page, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * @return HasMany<Page, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->where('published_at', '>', now());
    }

    /**
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    public function scopeRootLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->published_at > now();
    }

    /**
     * Get all ancestors of this page (parent, grandparent, etc.)
     *
     * @return Collection<int, Page>
     */
    public function getAncestors(): Collection
    {
        $ancestors = new Collection;
        $page = $this->parent;

        while ($page) {
            $ancestors->push($page);
            $page = $page->parent;
        }

        return $ancestors->reverse()->values();
    }

    /**
     * Generate breadcrumb trail for this page
     *
     * @return array<int, array{title: string, slug: string, url: string}>
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $ancestors = $this->getAncestors();

        foreach ($ancestors as $ancestor) {
            $breadcrumbs[] = [
                'title' => $ancestor->title,
                'slug' => $ancestor->slug,
                'url' => $ancestor->getUrl(),
            ];
        }

        $breadcrumbs[] = [
            'title' => $this->title,
            'slug' => $this->slug,
            'url' => $this->getUrl(),
        ];

        return $breadcrumbs;
    }

    /**
     * Get the full URL path for this page (including parent slugs)
     */
    public function getFullPath(): string
    {
        $segments = $this->getAncestors()->pluck('slug')->toArray();
        $segments[] = $this->slug;

        return implode('/', $segments);
    }

    /**
     * Get the URL for this page
     */
    public function getUrl(): string
    {
        return url($this->getFullPath());
    }

    /**
     * Check if setting a parent would create a circular reference
     */
    public function wouldCreateCircularReference(?int $parentId): bool
    {
        if ($parentId === null) {
            return false;
        }

        if ($parentId === $this->id) {
            return true;
        }

        $parent = static::find($parentId);
        while ($parent) {
            if ($parent->id === $this->id) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }

    /**
     * Get the depth level of this page in the hierarchy (0 = root)
     */
    public function getDepth(): int
    {
        return $this->getAncestors()->count();
    }

    public function getExcerptAttribute(?string $value): string
    {
        if ($value) {
            return $value;
        }

        return Str::limit(strip_tags($this->content ?? ''), 200);
    }

    protected static function booted(): void
    {
        static::creating(function (Page $page): void {
            if (empty($page->slug)) {
                $page->slug = static::generateUniqueSlug($page->title);
            }
        });

        static::updating(function (Page $page): void {
            if ($page->isDirty('title') && ! $page->isDirty('slug')) {
                $page->slug = static::generateUniqueSlug($page->title, $page->id);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }
}
