<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\PostObserver;
use App\Traits\HasRevisions;
use App\Traits\LogsActivityAllDirty;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

#[ObservedBy([PostObserver::class])]
class Post extends Model implements Feedable
{
    use HasFactory, HasRevisions, LogsActivityAllDirty, Searchable, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'author_id',
        'status',
        'published_at',
        'featured_image_id',
        'meta_title',
        'meta_description',
        'focus_keyword',
        'allow_comments',
        'view_count',
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
     * Get all feed items for the RSS feed.
     *
     * @return Collection<int, Post>
     */
    public static function getFeedItems(): Collection
    {
        return static::query()
            ->published()
            ->with(['author', 'categories', 'tags', 'featuredImage'])
            ->latest('published_at')
            ->take(20)
            ->get();
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
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->where('published_at', '>', now());
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

    public function getExcerptAttribute(?string $value): string
    {
        if ($value) {
            return $value;
        }

        return Str::limit(strip_tags($this->content), config('blog.posts.excerpt_length', 200));
    }

    /**
     * Get the indexable data array for the model.
     * Only include database columns for database driver compatibility.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->getRawOriginal('excerpt'),
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->isPublished();
    }

    /**
     * Convert the model to a feed item.
     */
    public function toFeedItem(): FeedItem
    {
        $categories = $this->categories->pluck('name')
            ->merge($this->tags->pluck('name'))
            ->toArray();

        $summary = $this->getRawOriginal('excerpt')
            ?: Str::limit(strip_tags($this->content ?? ''), 300);

        $feedItem = FeedItem::create()
            ->id((string) $this->id)
            ->title($this->title)
            ->summary($summary)
            ->updated($this->published_at ?? $this->created_at)
            ->link(route('posts.show', $this->slug))
            ->authorName($this->author?->name ?? config('app.name'))
            ->authorEmail($this->author?->email ?? '');

        if (count($categories) > 0) {
            $feedItem->category(...$categories);
        }

        if ($this->featuredImage?->url) {
            $feedItem->enclosure($this->featuredImage->url)
                ->enclosureType($this->featuredImage->mime_type ?? 'image/jpeg')
                ->enclosureLength($this->featuredImage->size ?? 0);
        }

        return $feedItem;
    }

    protected static function booted(): void
    {
        static::creating(function (Post $post): void {
            if (empty($post->slug)) {
                $post->slug = static::generateUniqueSlug($post->title);
            }
        });

        static::updating(function (Post $post): void {
            if ($post->isDirty('title') && ! $post->isDirty('slug')) {
                $post->slug = static::generateUniqueSlug($post->title, $post->id);
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
            'allow_comments' => 'boolean',
            'view_count' => 'integer',
        ];
    }
}
