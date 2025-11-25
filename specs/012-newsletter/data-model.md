# Data Model: Newsletter

## Database Schema

### subscribers

```
┌─────────────────────────────────────────────────────────┐
│                     subscribers                         │
├─────────────────────────────────────────────────────────┤
│ id                │ bigint PK auto_increment            │
│ email             │ string(255) unique                  │
│ name              │ string(255) nullable                │
│ unsubscribe_token │ string(64) unique                   │
│ verified_at       │ timestamp nullable                  │
│ subscribed_at     │ timestamp                           │
│ unsubscribed_at   │ timestamp nullable                  │
│ ip_address        │ string(45) nullable                 │
│ user_agent        │ text nullable                       │
│ source            │ string(50) default 'website'        │
│ created_at        │ timestamp                           │
│ updated_at        │ timestamp                           │
└─────────────────────────────────────────────────────────┘

Indexes:
- PRIMARY (id)
- UNIQUE (email)
- UNIQUE (unsubscribe_token)
- INDEX (verified_at)
- INDEX (subscribed_at)
```

### newsletters

```
┌─────────────────────────────────────────────────────────┐
│                     newsletters                         │
├─────────────────────────────────────────────────────────┤
│ id                │ bigint PK auto_increment            │
│ subject           │ string(255)                         │
│ content           │ longtext                            │
│ status            │ enum(draft,scheduled,sending,sent)  │
│ scheduled_at      │ timestamp nullable                  │
│ sent_at           │ timestamp nullable                  │
│ total_recipients  │ int unsigned default 0              │
│ sent_count        │ int unsigned default 0              │
│ failed_count      │ int unsigned default 0              │
│ created_by        │ bigint FK users.id                  │
│ created_at        │ timestamp                           │
│ updated_at        │ timestamp                           │
└─────────────────────────────────────────────────────────┘

Indexes:
- PRIMARY (id)
- INDEX (status)
- INDEX (scheduled_at)
- FOREIGN KEY (created_by) REFERENCES users(id)
```

### newsletter_sends (tracking)

```
┌─────────────────────────────────────────────────────────┐
│                   newsletter_sends                      │
├─────────────────────────────────────────────────────────┤
│ id                │ bigint PK auto_increment            │
│ newsletter_id     │ bigint FK newsletters.id            │
│ subscriber_id     │ bigint FK subscribers.id            │
│ status            │ enum(pending,sent,failed,bounced)   │
│ sent_at           │ timestamp nullable                  │
│ error_message     │ text nullable                       │
│ created_at        │ timestamp                           │
│ updated_at        │ timestamp                           │
└─────────────────────────────────────────────────────────┘

Indexes:
- PRIMARY (id)
- UNIQUE (newsletter_id, subscriber_id)
- INDEX (status)
- FOREIGN KEY (newsletter_id) REFERENCES newsletters(id) ON DELETE CASCADE
- FOREIGN KEY (subscriber_id) REFERENCES subscribers(id) ON DELETE CASCADE
```

## Migrations

### Create Subscribers Table

```php
Schema::create('subscribers', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->string('name')->nullable();
    $table->string('unsubscribe_token', 64)->unique();
    $table->timestamp('verified_at')->nullable();
    $table->timestamp('subscribed_at');
    $table->timestamp('unsubscribed_at')->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->string('source', 50)->default('website');
    $table->timestamps();

    $table->index('verified_at');
    $table->index('subscribed_at');
});
```

### Create Newsletters Table

```php
Schema::create('newsletters', function (Blueprint $table) {
    $table->id();
    $table->string('subject');
    $table->longText('content');
    $table->enum('status', ['draft', 'scheduled', 'sending', 'sent'])->default('draft');
    $table->timestamp('scheduled_at')->nullable();
    $table->timestamp('sent_at')->nullable();
    $table->unsignedInteger('total_recipients')->default(0);
    $table->unsignedInteger('sent_count')->default(0);
    $table->unsignedInteger('failed_count')->default(0);
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();

    $table->index('status');
    $table->index('scheduled_at');
});
```

### Create Newsletter Sends Table

```php
Schema::create('newsletter_sends', function (Blueprint $table) {
    $table->id();
    $table->foreignId('newsletter_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
    $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending');
    $table->timestamp('sent_at')->nullable();
    $table->text('error_message')->nullable();
    $table->timestamps();

    $table->unique(['newsletter_id', 'subscriber_id']);
    $table->index('status');
});
```

## Model Definitions

### Subscriber Model

```php
class Subscriber extends Model
{
    protected $fillable = [
        'email', 'name', 'unsubscribe_token', 'verified_at',
        'subscribed_at', 'unsubscribed_at', 'ip_address',
        'user_agent', 'source',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    // Scopes
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeActive($query)
    {
        return $query->verified()->whereNull('unsubscribed_at');
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscriber) {
            $subscriber->unsubscribe_token = Str::random(64);
            $subscriber->subscribed_at = now();
        });
    }

    // Relationships
    public function sends(): HasMany
    {
        return $this->hasMany(NewsletterSend::class);
    }
}
```

### Newsletter Model

```php
class Newsletter extends Model
{
    protected $fillable = [
        'subject', 'content', 'status', 'scheduled_at',
        'sent_at', 'total_recipients', 'sent_count',
        'failed_count', 'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sends(): HasMany
    {
        return $this->hasMany(NewsletterSend::class);
    }

    // Methods
    public function markAsSending(): void
    {
        $this->update(['status' => 'sending']);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
```

## Entity Relationships

```
┌─────────────┐     1:N     ┌──────────────────┐     N:1     ┌─────────────┐
│    User     │─────────────│   Newsletter     │─────────────│ Subscriber  │
│  (creator)  │             │                  │             │             │
└─────────────┘             └──────────────────┘             └─────────────┘
                                    │                               │
                                    │                               │
                                    │ 1:N                     N:1   │
                                    │                               │
                                    ▼                               │
                            ┌──────────────────┐                    │
                            │ NewsletterSend   │────────────────────┘
                            │ (tracking pivot) │
                            └──────────────────┘
```

## Status Flow

### Subscriber Status

```
[New] → subscribed_at set, verified_at null
   │
   ▼ (click verification link)
[Verified] → verified_at set
   │
   ▼ (click unsubscribe)
[Unsubscribed] → unsubscribed_at set
```

### Newsletter Status

```
[Draft] → Initial state
   │
   ├─── (set scheduled_at) ──▶ [Scheduled]
   │                               │
   │                               ▼ (scheduled time reached)
   │                          [Sending]
   │                               │
   ▼ (send immediately)            │
[Sending] ◀────────────────────────┘
   │
   ▼ (all emails processed)
[Sent] → sent_at set
```

## Indexes Strategy

| Index | Purpose | Query Pattern |
|-------|---------|---------------|
| subscribers.email | Unique lookup | Find by email |
| subscribers.verified_at | Filter active | Active subscribers |
| newsletters.status | Filter by status | Pending newsletters |
| newsletters.scheduled_at | Scheduler query | Due newsletters |
| newsletter_sends composite | Track per-newsletter | Delivery status |
