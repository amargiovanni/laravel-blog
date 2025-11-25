# Quickstart: Newsletter

## Prerequisites

- Laravel 12 with configured mail driver
- Queue worker configured (database, redis, etc.)
- Filament 4.x installed

## Installation Steps

### 1. Create Migrations

```bash
php artisan make:migration create_subscribers_table --no-interaction
php artisan make:migration create_newsletters_table --no-interaction
php artisan make:migration create_newsletter_sends_table --no-interaction
```

### 2. Create Models

```bash
php artisan make:model Subscriber --no-interaction
php artisan make:model Newsletter --no-interaction
php artisan make:model NewsletterSend --no-interaction
```

### 3. Create Subscriber Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    protected $fillable = [
        'email', 'name', 'unsubscribe_token', 'verified_at',
        'subscribed_at', 'unsubscribed_at', 'ip_address', 'source',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($subscriber) {
            $subscriber->unsubscribe_token = Str::random(64);
            $subscriber->subscribed_at = now();
        });
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeActive($query)
    {
        return $query->verified()->whereNull('unsubscribed_at');
    }
}
```

### 4. Create Controllers

```bash
php artisan make:controller SubscriptionController --no-interaction
php artisan make:controller UnsubscribeController --no-interaction
```

**SubscriptionController.php**:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Mail\SubscriptionConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $existing = Subscriber::where('email', $validated['email'])->first();

        if ($existing && $existing->verified_at) {
            return back()->with('message', 'You are already subscribed!');
        }

        $subscriber = Subscriber::updateOrCreate(
            ['email' => $validated['email']],
            [
                'ip_address' => $request->ip(),
                'source' => 'website',
            ]
        );

        $verificationUrl = URL::temporarySignedRoute(
            'newsletter.verify',
            now()->addHours(48),
            ['subscriber' => $subscriber->id]
        );

        Mail::to($subscriber->email)->send(
            new SubscriptionConfirmation($verificationUrl)
        );

        return back()->with('message', 'Please check your email to confirm subscription.');
    }

    public function verify(Request $request, Subscriber $subscriber)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired verification link.');
        }

        $subscriber->update(['verified_at' => now()]);

        return view('newsletter.verified');
    }
}
```

### 5. Create Mailable

```bash
php artisan make:mail SubscriptionConfirmation --no-interaction
```

```php
<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SubscriptionConfirmation extends Mailable
{
    public function __construct(public string $verificationUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirm Your Newsletter Subscription',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-confirmation',
        );
    }
}
```

### 6. Create Email Template

```blade
{{-- resources/views/emails/subscription-confirmation.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #2563eb;">Confirm Your Subscription</h1>

        <p>Thank you for subscribing to our newsletter!</p>

        <p>Please click the button below to confirm your subscription:</p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $verificationUrl }}"
               style="background: #2563eb; color: white; padding: 12px 24px;
                      text-decoration: none; border-radius: 6px;">
                Confirm Subscription
            </a>
        </p>

        <p style="color: #666; font-size: 14px;">
            If you didn't subscribe to this newsletter, you can safely ignore this email.
        </p>

        <p style="color: #666; font-size: 14px;">
            This link will expire in 48 hours.
        </p>
    </div>
</body>
</html>
```

### 7. Add Routes

```php
// routes/web.php

Route::post('newsletter/subscribe', [SubscriptionController::class, 'store'])
    ->name('newsletter.subscribe');

Route::get('newsletter/verify/{subscriber}', [SubscriptionController::class, 'verify'])
    ->name('newsletter.verify');

Route::get('newsletter/unsubscribe/{token}', [UnsubscribeController::class, 'show'])
    ->name('newsletter.unsubscribe');

Route::post('newsletter/unsubscribe/{token}', [UnsubscribeController::class, 'unsubscribe']);
```

### 8. Create Subscription Form Component

```blade
{{-- resources/views/components/newsletter-form.blade.php --}}
<div class="newsletter-form">
    <h3 class="text-xl font-bold mb-4">Subscribe to Newsletter</h3>

    @if(session('message'))
        <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <form action="{{ route('newsletter.subscribe') }}" method="POST" class="flex gap-2">
        @csrf
        <input type="email" name="email" required
               placeholder="Enter your email"
               class="flex-1 px-4 py-2 border rounded">
        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Subscribe
        </button>
    </form>

    @error('email')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
```

### 9. Create Filament Resources

```bash
php artisan make:filament-resource Subscriber --no-interaction
php artisan make:filament-resource Newsletter --no-interaction
```

### 10. Run Migrations

```bash
php artisan migrate
```

## Verification

```bash
# Test subscription
curl -X POST http://localhost/newsletter/subscribe \
  -d "email=test@example.com" \
  -d "_token=$(php artisan tinker --execute="echo csrf_token()")"

# Check subscriber in database
php artisan tinker --execute="App\Models\Subscriber::all()"
```

## Next Steps

1. Implement UnsubscribeController
2. Create newsletter composer in Filament
3. Add SendNewsletterJob for bulk sending
4. Implement new post notification
5. Add CSV export functionality
