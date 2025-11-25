# Research: Contact Form

## Spam Protection Strategies

### 1. Honeypot Fields (Recommended)

A hidden field that bots fill but humans don't see.

**Implementation**:

```html
<div style="display: none;">
    <label for="website">Website</label>
    <input type="text" name="website" id="website" autocomplete="off">
</div>
```

```php
// In FormRequest or Controller
public function rules(): array
{
    return [
        'website' => 'max:0', // Must be empty
        'name' => 'required|string|max:255',
        // ... other fields
    ];
}
```

**Pros**:
- No impact on user experience
- Works without JavaScript
- No third-party dependency

**Cons**:
- Sophisticated bots may bypass

### 2. Time-Based Validation

Reject submissions that happen too quickly (bots fill forms instantly).

```php
// Add timestamp to form
<input type="hidden" name="form_started" value="{{ encrypt(now()->timestamp) }}">

// Validate minimum time
$started = decrypt($request->form_started);
if (now()->timestamp - $started < 3) {
    // Reject - too fast for human
}
```

### 3. Rate Limiting

Laravel's built-in rate limiting:

```php
// In RouteServiceProvider or route definition
Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1'); // 5 attempts per minute
```

Or custom limiter:

```php
RateLimiter::for('contact', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

### 4. CAPTCHA (Optional)

For high-traffic sites, consider Google reCAPTCHA:

```bash
composer require google/recaptcha
```

**Recommendation**: Start with honeypot + rate limiting. Add CAPTCHA only if spam becomes a problem.

## Input Validation

### Laravel Form Request

```php
class ContactFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['max:0'], // Honeypot
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'message.required' => 'Please enter your message.',
            'message.min' => 'Your message must be at least 10 characters.',
            'message.max' => 'Your message cannot exceed 5000 characters.',
        ];
    }
}
```

### Email Validation

Use `email:rfc,dns` for stricter validation:
- `rfc` - Validates against RFC 5321
- `dns` - Checks domain has MX records

## XSS Prevention

### Input Sanitization

```php
use Illuminate\Support\Str;

$message = Str::of($request->message)
    ->stripTags()
    ->trim();
```

### Output Escaping

Always escape in views:

```blade
{{-- Escaped by default --}}
{{ $message->content }}

{{-- For admin, if HTML needed, use purifier --}}
{!! clean($message->content) !!}
```

### Content Security Policy

Add CSP headers:

```php
// In middleware or response
$response->headers->set(
    'Content-Security-Policy',
    "default-src 'self'; script-src 'self'"
);
```

## Email Notification

### Mailable Structure

```php
class ContactFormSubmission extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $message
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Contact Form] {$this->message->subject}",
            replyTo: [$this->message->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-submission',
        );
    }
}
```

### Email Template

```blade
{{-- resources/views/emails/contact-submission.blade.php --}}
<h2>New Contact Form Submission</h2>

<p><strong>From:</strong> {{ $message->name }} ({{ $message->email }})</p>
<p><strong>Subject:</strong> {{ $message->subject }}</p>
<p><strong>Date:</strong> {{ $message->created_at->format('F j, Y g:i A') }}</p>

<hr>

<p><strong>Message:</strong></p>
<div style="background: #f5f5f5; padding: 15px; border-radius: 5px;">
    {!! nl2br(e($message->message)) !!}
</div>

<hr>

<p style="color: #666; font-size: 12px;">
    IP Address: {{ $message->ip_address }}
</p>
```

## Admin Interface (Filament)

### ContactMessageResource

```php
class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationBadgeTooltip = 'Unread messages';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_read', false)->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->disabled(),
            TextInput::make('email')->disabled(),
            TextInput::make('subject')->disabled(),
            Textarea::make('message')->disabled()->rows(10),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('subject')->limit(30),
                IconColumn::make('is_read')
                    ->boolean()
                    ->label('Read'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),
                Action::make('markAsRead')
                    ->icon('heroicon-o-check')
                    ->action(fn($record) => $record->update(['is_read' => true]))
                    ->hidden(fn($record) => $record->is_read),
                DeleteAction::make(),
            ]);
    }
}
```

## Form Component (Livewire/Volt)

```php
<?php

use function Livewire\Volt\{state, rules};
use App\Models\ContactMessage;
use App\Mail\ContactFormSubmission;
use Illuminate\Support\Facades\Mail;

state([
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => '',
    'website' => '', // Honeypot
    'submitted' => false,
]);

rules([
    'name' => 'required|string|max:255',
    'email' => 'required|email:rfc,dns|max:255',
    'subject' => 'required|string|max:255',
    'message' => 'required|string|min:10|max:5000',
    'website' => 'max:0',
]);

$submit = function () {
    $this->validate();

    $contactMessage = ContactMessage::create([
        'name' => $this->name,
        'email' => $this->email,
        'subject' => $this->subject,
        'message' => $this->message,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);

    try {
        Mail::to(config('mail.admin_address'))
            ->send(new ContactFormSubmission($contactMessage));
    } catch (\Exception $e) {
        report($e);
    }

    $this->submitted = true;
    $this->reset(['name', 'email', 'subject', 'message']);
};
?>
```

## Performance Considerations

| Operation | Target | Strategy |
|-----------|--------|----------|
| Form render | < 100ms | Static page with component |
| Form submission | < 2s | Queue email sending |
| Admin list load | < 500ms | Pagination, indexes |

## Security Checklist

- [ ] CSRF token on form
- [ ] Honeypot field implemented
- [ ] Rate limiting configured
- [ ] Input sanitization
- [ ] Output escaping in admin
- [ ] Email not exposing internal paths
- [ ] No SQL injection (use Eloquent)
- [ ] HTTPS enforced
