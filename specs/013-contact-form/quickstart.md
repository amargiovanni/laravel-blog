# Quickstart: Contact Form

## Prerequisites

- Laravel 12 with configured mail driver
- Filament 4.x installed
- Public frontend layout from 007-public-frontend

## Installation Steps

### 1. Create Migration

```bash
php artisan make:migration create_contact_messages_table --no-interaction
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

            $table->index(['is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
```

### 2. Create Model

```bash
php artisan make:model ContactMessage --no-interaction
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name', 'email', 'subject', 'message',
        'ip_address', 'user_agent', 'is_read', 'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }
}
```

### 3. Create Form Request

```bash
php artisan make:request ContactFormRequest --no-interaction
```

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'website.max' => '', // Silent rejection for bots
        ];
    }
}
```

### 4. Create Controller

```bash
php artisan make:controller ContactController --no-interaction
```

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Mail\ContactFormSubmission;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function store(ContactFormRequest $request)
    {
        $message = ContactMessage::create([
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => strip_tags($request->message),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            Mail::to(config('mail.admin_address'))
                ->send(new ContactFormSubmission($message));
        } catch (\Exception $e) {
            report($e);
        }

        return back()->with('success', 'Thank you for your message! We\'ll get back to you soon.');
    }
}
```

### 5. Create Mailable

```bash
php artisan make:mail ContactFormSubmission --no-interaction
```

```php
<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactFormSubmission extends Mailable
{
    public function __construct(
        public ContactMessage $message
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Contact] {$this->message->subject}",
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

### 6. Create Email Template

```blade
{{-- resources/views/emails/contact-submission.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">New Contact Form Submission</h2>

        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td style="padding: 8px 0; font-weight: bold; width: 100px;">From:</td>
                <td>{{ $message->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">Email:</td>
                <td><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">Subject:</td>
                <td>{{ $message->subject }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">Date:</td>
                <td>{{ $message->created_at->format('F j, Y g:i A') }}</td>
            </tr>
        </table>

        <div style="background: #f5f5f5; padding: 20px; border-radius: 8px;">
            <h3 style="margin-top: 0;">Message:</h3>
            <p style="white-space: pre-wrap;">{{ $message->message }}</p>
        </div>

        <p style="margin-top: 20px; color: #666; font-size: 12px;">
            IP: {{ $message->ip_address }}
        </p>
    </div>
</body>
</html>
```

### 7. Create Contact Page View

```blade
{{-- resources/views/contact.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold mb-8">Contact Us</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Honeypot --}}
        <div style="display: none;">
            <input type="text" name="website" autocomplete="off">
        </div>

        <div>
            <label for="name" class="block font-medium mb-2">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}"
                   class="w-full px-4 py-2 border rounded-lg @error('name') border-red-500 @enderror">
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block font-medium mb-2">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                   class="w-full px-4 py-2 border rounded-lg @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="subject" class="block font-medium mb-2">Subject</label>
            <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                   class="w-full px-4 py-2 border rounded-lg @error('subject') border-red-500 @enderror">
            @error('subject')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="message" class="block font-medium mb-2">Message</label>
            <textarea name="message" id="message" rows="6"
                      class="w-full px-4 py-2 border rounded-lg @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
            @error('message')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Send Message
        </button>
    </form>
</div>
@endsection
```

### 8. Add Routes

```php
// routes/web.php

Route::get('contact', [ContactController::class, 'show'])->name('contact');
Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');
```

### 9. Create Filament Resource

```bash
php artisan make:filament-resource ContactMessage --no-interaction
```

### 10. Run Migrations

```bash
php artisan migrate
```

## Configuration

Add to `config/mail.php` or `.env`:

```env
MAIL_ADMIN_ADDRESS=admin@yourblog.com
```

```php
// config/mail.php
'admin_address' => env('MAIL_ADMIN_ADDRESS', 'admin@example.com'),
```

## Verification

```bash
# Test form
curl -X POST http://localhost/contact \
  -d "name=Test User" \
  -d "email=test@example.com" \
  -d "subject=Test Subject" \
  -d "message=This is a test message." \
  -d "_token=$(php artisan tinker --execute="echo csrf_token()")"

# Check database
php artisan tinker --execute="App\Models\ContactMessage::all()"
```

## Next Steps

1. Customize Filament resource for better UX
2. Add unread badge to admin navigation
3. Consider adding CAPTCHA if spam becomes an issue
4. Add email queue for async sending
