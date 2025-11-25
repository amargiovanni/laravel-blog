# Data Model: Comments System

## Database Schema

### comments

```
┌─────────────────────────────────────────────────────────┐
│                       comments                          │
├─────────────────────────────────────────────────────────┤
│ id                │ bigint PK auto_increment            │
│ post_id           │ bigint FK -> posts.id               │
│ parent_id         │ bigint FK -> comments.id nullable   │
│ user_id           │ bigint FK -> users.id nullable      │
│ author_name       │ string(255)                         │
│ author_email      │ string(255)                         │
│ author_url        │ string(255) nullable                │
│ content           │ text                                │
│ status            │ enum: pending,approved,rejected,spam│
│ ip_address        │ string(45) nullable                 │
│ user_agent        │ text nullable                       │
│ is_notify_replies │ boolean default true                │
│ approved_at       │ timestamp nullable                  │
│ created_at        │ timestamp                           │
│ updated_at        │ timestamp                           │
└─────────────────────────────────────────────────────────┘

Indexes:
- PRIMARY (id)
- INDEX (post_id)
- INDEX (parent_id)
- INDEX (user_id)
- INDEX (status)
- INDEX (author_email)
- INDEX (created_at)
- COMPOSITE (post_id, status, created_at)
```

### posts (modifications)

```
┌─────────────────────────────────────────────────────────┐
│                    posts (additions)                    │
├─────────────────────────────────────────────────────────┤
│ comments_enabled  │ boolean default true                │
│ comments_count    │ int unsigned default 0              │
└─────────────────────────────────────────────────────────┘
```

## Migration

### Create Comments Table

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
    $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('author_name');
    $table->string('author_email');
    $table->string('author_url')->nullable();
    $table->text('content');
    $table->enum('status', ['pending', 'approved', 'rejected', 'spam'])->default('pending');
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->boolean('is_notify_replies')->default(true);
    $table->timestamp('approved_at')->nullable();
    $table->timestamps();

    $table->index('status');
    $table->index('author_email');
    $table->index(['post_id', 'status', 'created_at']);
});
```

### Add Comments Fields to Posts

```php
Schema::table('posts', function (Blueprint $table) {
    $table->boolean('comments_enabled')->default(true)->after('is_featured');
    $table->unsignedInteger('comments_count')->default(0)->after('comments_enabled');
});
```

## Model Definition

### Comment Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

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

    protected function casts(): array
    {
        return [
            'is_notify_replies' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function approvedReplies(): HasMany
    {
        return $this->replies()->approved();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeSpam(Builder $query): Builder
    {
        return $query->where('status', 'spam');
    }

    public function scopeRootLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    // Methods
    public function approve(): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
        $this->post->incrementCommentsCount();
    }

    public function reject(): void
    {
        $wasApproved = $this->status === 'approved';
        $this->update(['status' => 'rejected']);
        if ($wasApproved) {
            $this->post->decrementCommentsCount();
        }
    }

    public function markAsSpam(): void
    {
        $wasApproved = $this->status === 'approved';
        $this->update(['status' => 'spam']);
        if ($wasApproved) {
            $this->post->decrementCommentsCount();
        }
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    public function getDepth(): int
    {
        $depth = 0;
        $comment = $this;
        while ($comment->parent_id !== null) {
            $depth++;
            $comment = $comment->parent;
        }
        return $depth;
    }

    public function getGravatarUrl(int $size = 48): string
    {
        $hash = md5(strtolower(trim($this->author_email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=mp";
    }

    // Accessors
    public function getAuthorDisplayNameAttribute(): string
    {
        return $this->user ? $this->user->name : $this->author_name;
    }
}
```

### Post Model Additions

```php
// Add to Post model

public function comments(): HasMany
{
    return $this->hasMany(Comment::class);
}

public function approvedComments(): HasMany
{
    return $this->comments()->approved()->rootLevel();
}

public function pendingComments(): HasMany
{
    return $this->comments()->pending();
}

public function incrementCommentsCount(): void
{
    $this->increment('comments_count');
}

public function decrementCommentsCount(): void
{
    $this->decrement('comments_count');
}

public function commentsAreEnabled(): bool
{
    if (!$this->comments_enabled) {
        return false;
    }

    // Check auto-close setting
    $autoCloseDays = config('comments.auto_close_days');
    if ($autoCloseDays && $this->published_at?->diffInDays(now()) > $autoCloseDays) {
        return false;
    }

    return config('comments.enabled', true);
}
```

## Comment Status Flow

```
[New Submission]
       │
       ▼
   [Pending] ◄────────────────┐
       │                       │
       ├──► [Approved] ──────►│ (can be reverted)
       │         │             │
       ├──► [Rejected] ──────►│
       │                       │
       └──► [Spam] ───────────┘
```

## Form Data Structure

### Comment Submission

| Field | Type | Required | Validation | Notes |
|-------|------|----------|------------|-------|
| post_id | hidden | Yes | exists:posts,id | From context |
| parent_id | hidden | No | exists:comments,id | For replies |
| author_name | text | Yes* | string, max:255 | Not if logged in |
| author_email | email | Yes* | email:rfc,dns, max:255 | Not if logged in |
| author_url | url | No | url, max:255 | Optional website |
| content | textarea | Yes | string, min:3, max:2000 | |
| website | hidden | No | max:0 | Honeypot trap |

### Validation Rules

```php
[
    'post_id' => ['required', 'exists:posts,id'],
    'parent_id' => ['nullable', 'exists:comments,id'],
    'author_name' => ['required_without:user_id', 'string', 'max:255'],
    'author_email' => ['required_without:user_id', 'email:rfc,dns', 'max:255'],
    'author_url' => ['nullable', 'url', 'max:255'],
    'content' => ['required', 'string', 'min:3', 'max:2000'],
    'website' => ['max:0'], // Honeypot - must be empty
]
```

## Threading Configuration

```php
// config/comments.php

return [
    'enabled' => env('COMMENTS_ENABLED', true),
    'moderation' => env('COMMENTS_MODERATION', true), // Require approval
    'max_depth' => env('COMMENTS_MAX_DEPTH', 3),
    'auto_close_days' => env('COMMENTS_AUTO_CLOSE_DAYS', null), // null = never
    'rate_limit' => [
        'max_attempts' => 5,
        'decay_minutes' => 1,
    ],
    'notifications' => [
        'new_comment' => true,
        'reply' => true,
    ],
    'auto_hold_with_links' => true,
    'max_length' => 2000,
];
```

## Admin Display

### List View Columns

| Column | Type | Sortable | Searchable |
|--------|------|----------|------------|
| author_name | text | No | Yes |
| author_email | text | No | Yes |
| content | text (truncated) | No | Yes |
| post.title | relationship | No | Yes |
| status | badge | Yes | Filter |
| created_at | datetime | Yes | No |

### Filters

| Filter | Type | Description |
|--------|------|-------------|
| Status | Select | Pending / Approved / Rejected / Spam |
| Post | Select | Filter by specific post |
| Date Range | DatePicker | Filter by created_at |

### Actions

| Action | Icon | Condition | Description |
|--------|------|-----------|-------------|
| View | eye | Always | View full comment |
| Approve | check | status != approved | Approve comment |
| Reject | x-mark | status != rejected | Reject comment |
| Mark Spam | shield-exclamation | status != spam | Mark as spam |
| Delete | trash | Always | Permanently delete |
| Reply | reply | status = approved | Admin reply |

### Bulk Actions

- Approve selected
- Reject selected
- Mark as spam
- Delete selected

## Notification Data

### NewCommentNotification

```php
// Data passed to notification
[
    'comment' => $comment,
    'post' => $comment->post,
    'url' => route('posts.show', $comment->post) . '#comment-' . $comment->id,
]
```

### CommentReplyNotification

```php
// Data passed to notification
[
    'comment' => $reply,
    'parent' => $reply->parent,
    'post' => $reply->post,
    'url' => route('posts.show', $reply->post) . '#comment-' . $reply->id,
]
```

## Security Considerations

### Input Sanitization

```php
// Strip HTML tags, preserve newlines
$content = strip_tags($request->content);
$content = nl2br(e($content)); // For display only
```

### Output Escaping

```blade
{{-- In comment display --}}
{{ $comment->author_name }}
{{ $comment->author_email }}

{{-- Content with preserved line breaks --}}
{!! nl2br(e($comment->content)) !!}
```

### Rate Limiting

```php
// In CommentForm Livewire component
RateLimiter::for('comments', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

## Eager Loading Strategy

```php
// For post page with comments
$post->load([
    'approvedComments' => function ($query) {
        $query->with(['user', 'approvedReplies.user'])
              ->orderBy('created_at', 'asc');
    }
]);

// For admin list
Comment::with(['post:id,title,slug', 'user:id,name', 'parent:id,author_name'])
    ->latest()
    ->paginate(25);
```
