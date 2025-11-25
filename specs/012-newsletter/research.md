# Research: Newsletter

## Email Delivery Strategy

### Laravel Mail Configuration

Laravel provides multiple mail drivers:

1. **SMTP** - Traditional, works with most providers
2. **Mailgun** - Reliable, good deliverability
3. **Amazon SES** - Cost-effective for volume
4. **Postmark** - Excellent for transactional
5. **SendGrid** - Popular, good analytics

**Recommendation**: Use environment-based configuration to support any provider.

### Queue-Based Sending

```php
// Send via queue for bulk operations
Mail::to($subscriber)->queue(new NewsletterMail($newsletter));

// Or dispatch dedicated job for batching
SendNewsletterJob::dispatch($newsletter)->onQueue('newsletter');
```

### Batch Processing

For large subscriber lists, process in chunks:

```php
Subscriber::active()
    ->chunk(100, function ($subscribers) use ($newsletter) {
        foreach ($subscribers as $subscriber) {
            SendNewsletterEmail::dispatch($subscriber, $newsletter)
                ->delay(now()->addSeconds(rand(0, 60)));
        }
    });
```

## Double Opt-In Implementation

### Flow

1. User submits email
2. Create subscriber with `verified_at = null`
3. Send confirmation email with signed URL
4. User clicks link
5. Update `verified_at = now()`

### Signed URL Generation

```php
use Illuminate\Support\Facades\URL;

$verificationUrl = URL::temporarySignedRoute(
    'newsletter.verify',
    now()->addHours(48),
    ['subscriber' => $subscriber->id]
);
```

### Verification Controller

```php
public function verify(Request $request, Subscriber $subscriber)
{
    if (!$request->hasValidSignature()) {
        abort(401, 'Invalid or expired verification link.');
    }

    $subscriber->update(['verified_at' => now()]);

    return view('newsletter.verified');
}
```

## Unsubscribe Mechanism

### Token-Based Unsubscribe

Generate unique, unguessable tokens:

```php
// In Subscriber model
public static function boot()
{
    parent::boot();

    static::creating(function ($subscriber) {
        $subscriber->unsubscribe_token = Str::random(64);
    });
}
```

### One-Click Unsubscribe

```php
Route::get('unsubscribe/{token}', [UnsubscribeController::class, 'show'])
    ->name('newsletter.unsubscribe');

Route::post('unsubscribe/{token}', [UnsubscribeController::class, 'unsubscribe']);
```

### List-Unsubscribe Header

Add header for email client one-click unsubscribe:

```php
// In Mailable
public function headers(): Headers
{
    return new Headers(
        text: [
            'List-Unsubscribe' => '<' . route('newsletter.unsubscribe', $this->token) . '>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ],
    );
}
```

## Legal Compliance

### CAN-SPAM Requirements

1. ✅ Clear identification as advertisement (if applicable)
2. ✅ Valid physical postal address
3. ✅ Clear unsubscribe mechanism
4. ✅ Honor opt-out requests within 10 business days
5. ✅ No misleading headers or subject lines

### GDPR Requirements

1. ✅ Double opt-in (explicit consent)
2. ✅ Record consent date and method
3. ✅ Easy withdrawal of consent
4. ✅ Data export capability
5. ✅ Right to deletion

### Implementation

```php
// Store consent metadata
$subscriber = Subscriber::create([
    'email' => $email,
    'ip_address' => $request->ip(),
    'consent_given_at' => now(),
    'consent_method' => 'website_form',
]);
```

## Filament Admin Implementation

### SubscriberResource

```php
class SubscriberResource extends Resource
{
    protected static ?string $model = Subscriber::class;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('email')->email()->required(),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')->searchable(),
                IconColumn::make('verified_at')->boolean(),
                TextColumn::make('subscribed_at')->dateTime(),
            ])
            ->filters([
                Filter::make('verified')->query(fn($q) => $q->whereNotNull('verified_at')),
                Filter::make('unverified')->query(fn($q) => $q->whereNull('verified_at')),
            ])
            ->actions([
                DeleteAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                DeleteBulkAction::make(),
            ]);
    }
}
```

### Newsletter Composer

```php
class NewsletterResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('subject')->required(),
            RichEditor::make('content')->required(),
            DateTimePicker::make('scheduled_at'),
            Select::make('status')
                ->options(['draft', 'scheduled', 'sent'])
                ->default('draft'),
        ]);
    }
}
```

## Email Templates

### Base Layout

```blade
{{-- resources/views/emails/layout.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        {{-- Header --}}
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="color: #2563eb;">{{ config('app.name') }}</h1>
        </div>

        {{-- Content --}}
        @yield('content')

        {{-- Footer --}}
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666;">
            <p>You're receiving this because you subscribed to our newsletter.</p>
            <p><a href="{{ $unsubscribeUrl }}">Unsubscribe</a></p>
            <p>{{ config('app.name') }} | {{ config('newsletter.address') }}</p>
        </div>
    </div>
</body>
</html>
```

## New Post Notification

### Event Listener

```php
// In EventServiceProvider or Post model observer
Post::saved(function (Post $post) {
    if ($post->wasChanged('status') && $post->status === 'published') {
        if (config('newsletter.notify_on_new_post')) {
            SendNewPostNotificationJob::dispatch($post);
        }
    }
});
```

### Job Implementation

```php
class SendNewPostNotificationJob implements ShouldQueue
{
    public function __construct(public Post $post) {}

    public function handle(): void
    {
        Subscriber::verified()->chunk(100, function ($subscribers) {
            foreach ($subscribers as $subscriber) {
                Mail::to($subscriber->email)
                    ->queue(new NewPostNotification($this->post, $subscriber));
            }
        });
    }
}
```

## Rate Limiting

### Queue Rate Limiting

```php
// In job class
public function middleware(): array
{
    return [
        new RateLimited('newsletter'),
    ];
}

// In AppServiceProvider
RateLimiter::for('newsletter', function ($job) {
    return Limit::perMinute(100); // 100 emails per minute
});
```

## Monitoring & Analytics

### Track Delivery Status

```php
// Add to newsletter_subscribers pivot
Schema::create('newsletter_sends', function (Blueprint $table) {
    $table->id();
    $table->foreignId('newsletter_id');
    $table->foreignId('subscriber_id');
    $table->enum('status', ['pending', 'sent', 'failed', 'bounced']);
    $table->timestamp('sent_at')->nullable();
    $table->text('error')->nullable();
    $table->timestamps();
});
```

## Performance Considerations

| Metric | Target | Strategy |
|--------|--------|----------|
| Subscription response | < 500ms | Async email sending |
| Newsletter send start | < 5s | Queue immediately |
| 10k emails | < 2 hours | Batching, rate limiting |
| Admin list load | < 2s | Pagination, eager loading |
