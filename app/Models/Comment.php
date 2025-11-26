<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\LogsActivityAllDirty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory, LogsActivityAllDirty;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SPAM = 'spam';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'parent_id',
        'user_id',
        'author_name',
        'author_email',
        'author_url',
        'content',
        'status',
        'ip_address',
        'user_agent',
        'is_notify_replies',
        'approved_at',
    ];

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function approvedReplies(): HasMany
    {
        return $this->replies()->approved();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<Comment>  $query
     * @return Builder<Comment>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * @param  Builder<Comment>  $query
     * @return Builder<Comment>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * @param  Builder<Comment>  $query
     * @return Builder<Comment>
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * @param  Builder<Comment>  $query
     * @return Builder<Comment>
     */
    public function scopeSpam(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SPAM);
    }

    /**
     * @param  Builder<Comment>  $query
     * @return Builder<Comment>
     */
    public function scopeRootLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Approve the comment.
     */
    public function approve(): bool
    {
        $wasApproved = $this->isApproved();

        $result = $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        if ($result && ! $wasApproved) {
            $this->post?->increment('comments_count');
        }

        return $result;
    }

    /**
     * Reject the comment.
     */
    public function reject(): bool
    {
        $wasApproved = $this->isApproved();

        $result = $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_at' => null,
        ]);

        if ($result && $wasApproved) {
            $this->post?->decrement('comments_count');
        }

        return $result;
    }

    /**
     * Mark comment as spam.
     */
    public function markAsSpam(): bool
    {
        $wasApproved = $this->isApproved();

        $result = $this->update([
            'status' => self::STATUS_SPAM,
            'approved_at' => null,
        ]);

        if ($result && $wasApproved) {
            $this->post?->decrement('comments_count');
        }

        return $result;
    }

    /**
     * Check if comment is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if comment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if this is a reply to another comment.
     */
    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Get the depth of this comment in the thread.
     */
    public function getDepth(): int
    {
        $depth = 0;
        $comment = $this;

        while ($comment->parent_id !== null) {
            $depth++;
            $comment = $comment->parent;

            if (! $comment) {
                break;
            }
        }

        return $depth;
    }

    /**
     * Get Gravatar URL for the commenter.
     */
    public function getGravatarUrl(?int $size = null): string
    {
        $config = config('comments.gravatar');
        $size = $size ?? $config['size'] ?? 48;
        $default = $config['default'] ?? 'mp';
        $rating = $config['rating'] ?? 'g';

        $hash = md5(strtolower(trim($this->author_email)));

        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d={$default}&r={$rating}";
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_notify_replies' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }
}
