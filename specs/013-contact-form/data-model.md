# Data Model: Contact Form

## Database Schema

### contact_messages

```
┌─────────────────────────────────────────────────────────┐
│                   contact_messages                      │
├─────────────────────────────────────────────────────────┤
│ id                │ bigint PK auto_increment            │
│ name              │ string(255)                         │
│ email             │ string(255)                         │
│ subject           │ string(255)                         │
│ message           │ text                                │
│ ip_address        │ string(45) nullable                 │
│ user_agent        │ text nullable                       │
│ is_read           │ boolean default false               │
│ read_at           │ timestamp nullable                  │
│ created_at        │ timestamp                           │
│ updated_at        │ timestamp                           │
└─────────────────────────────────────────────────────────┘

Indexes:
- PRIMARY (id)
- INDEX (is_read)
- INDEX (created_at)
- INDEX (email)
```

## Migration

```php
Schema::create('contact_messages', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('subject');
    $table->text('message');
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->boolean('is_read')->default(false);
    $table->timestamp('read_at')->nullable();
    $table->timestamps();

    $table->index('is_read');
    $table->index('created_at');
    $table->index('email');
});
```

## Model Definition

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'ip_address',
        'user_agent',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Scopes
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    // Methods
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    // Accessors
    public function getSummaryAttribute(): string
    {
        return Str::limit($this->message, 100);
    }
}
```

## Form Data Structure

### Input Fields

| Field | Type | Required | Validation | Max Length |
|-------|------|----------|------------|------------|
| name | text | Yes | string | 255 |
| email | email | Yes | email:rfc,dns | 255 |
| subject | text | Yes | string | 255 |
| message | textarea | Yes | string, min:10 | 5000 |
| website | hidden | No | max:0 (honeypot) | - |

### Validation Rules

```php
[
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email:rfc,dns', 'max:255'],
    'subject' => ['required', 'string', 'max:255'],
    'message' => ['required', 'string', 'min:10', 'max:5000'],
    'website' => ['max:0'], // Honeypot - must be empty
]
```

## Message Status Flow

```
[New] → is_read = false, read_at = null
   │
   ▼ (admin views message)
[Read] → is_read = true, read_at = timestamp
   │
   ▼ (admin marks as unread)
[Unread] → is_read = false, read_at = null
```

## Email Notification Data

### Data Passed to Mailable

```php
ContactFormSubmission {
    public ContactMessage $message;

    // Available in template:
    // $message->name
    // $message->email
    // $message->subject
    // $message->message
    // $message->ip_address
    // $message->created_at
}
```

### Email Subject Format

```
[Contact Form] {subject}
```

### Reply-To Header

```php
replyTo: [$message->email]
```

## Admin Display

### List View Columns

| Column | Type | Sortable | Searchable |
|--------|------|----------|------------|
| name | text | No | Yes |
| email | text | No | Yes |
| subject | text (truncated) | No | Yes |
| is_read | boolean icon | No | Filter |
| created_at | datetime | Yes | No |

### Filters

| Filter | Type | Description |
|--------|------|-------------|
| Read Status | Select | All / Read / Unread |
| Date Range | DatePicker | Filter by created_at |

### Actions

| Action | Icon | Condition | Description |
|--------|------|-----------|-------------|
| View | eye | Always | View full message |
| Mark as Read | check | is_read = false | Mark message read |
| Mark as Unread | x-mark | is_read = true | Mark message unread |
| Delete | trash | Always | Delete message |

## Statistics (Optional)

### Dashboard Widgets

```php
// Unread messages count
ContactMessage::unread()->count();

// Messages this week
ContactMessage::whereBetween('created_at', [
    now()->startOfWeek(),
    now()->endOfWeek(),
])->count();

// Messages by day (for chart)
ContactMessage::selectRaw('DATE(created_at) as date, COUNT(*) as count')
    ->groupBy('date')
    ->orderBy('date', 'desc')
    ->limit(30)
    ->get();
```

## Security Considerations

### Input Sanitization

```php
// Strip HTML tags from message
$message = strip_tags($request->message);

// Or use Laravel's built-in
$message = Str::of($request->message)->stripTags();
```

### Output Escaping

```blade
{{-- In admin views --}}
{{ $message->name }}
{{ $message->email }}
{{ $message->subject }}

{{-- Message with line breaks --}}
{!! nl2br(e($message->message)) !!}
```

## Indexes Strategy

| Index | Purpose | Query Pattern |
|-------|---------|---------------|
| is_read | Filter unread | Admin badge count |
| created_at | Sort by date | Default list order |
| email | Search by sender | Find related messages |
