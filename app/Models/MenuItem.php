<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\LogsActivityAllDirty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MenuItem extends Model
{
    /** @use HasFactory<\Database\Factories\MenuItemFactory> */
    use HasFactory, LogsActivityAllDirty;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'linkable_type',
        'linkable_id',
        'url',
        'target',
        'css_class',
        'title_attribute',
        'sort_order',
    ];

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * @return BelongsTo<MenuItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getUrl(): string
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->linkable) {
            return $this->getLinkableUrl();
        }

        return '#';
    }

    public function getDisplayLabel(): string
    {
        if ($this->label) {
            return $this->label;
        }

        if ($this->linkable && method_exists($this->linkable, 'getTitle')) {
            return $this->linkable->getTitle();
        }

        if ($this->linkable && isset($this->linkable->title)) {
            return $this->linkable->title;
        }

        if ($this->linkable && isset($this->linkable->name)) {
            return $this->linkable->name;
        }

        return 'Untitled';
    }

    public function getDepth(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    public function canHaveChildren(): bool
    {
        return $this->getDepth() < 2; // Max 3 levels (0, 1, 2)
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected function getLinkableUrl(): string
    {
        $linkable = $this->linkable;

        if (! $linkable) {
            return '#';
        }

        return match ($this->linkable_type) {
            Page::class => route('pages.show', $linkable->slug),
            Post::class => route('posts.show', $linkable->slug),
            Category::class => route('categories.show', $linkable->slug),
            Tag::class => route('tags.show', $linkable->slug),
            default => '#',
        };
    }
}
