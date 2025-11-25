# Route Contracts: Contact Form

## Public Routes

### GET /contact

**Purpose**: Display contact form page

**Controller**: `ContactController@show`

**Response**: View `contact.blade.php`

**Status Codes**:
- 200: Page displayed successfully

---

### POST /contact

**Purpose**: Process contact form submission

**Controller**: `ContactController@store`

**Middleware**:
- `web`
- `throttle:5,1` (5 attempts per minute per IP)

**Request Body**:
```
name: "John Doe"
email: "john@example.com"
subject: "Inquiry about services"
message: "Hello, I would like to know..."
website: ""  (honeypot - must be empty)
_token: "csrf_token"
```

**Validation Rules**:
| Field | Rules |
|-------|-------|
| name | required, string, max:255 |
| email | required, email:rfc,dns, max:255 |
| subject | required, string, max:255 |
| message | required, string, min:10, max:5000 |
| website | max:0 (honeypot) |

**Response**:
- Success: Redirect back with flash `success` message
- Validation error: Redirect back with errors and old input
- Rate limited (429): Too many requests error

---

## Admin Routes (Filament)

### Contact Messages Management

| Route | Method | Description |
|-------|--------|-------------|
| /admin/contact-messages | GET | List all messages |
| /admin/contact-messages/{id} | GET | View single message |
| /admin/contact-messages/{id} | DELETE | Delete message |

### Custom Actions

| Route | Method | Description |
|-------|--------|-------------|
| /admin/contact-messages/{id}/mark-read | POST | Mark as read |
| /admin/contact-messages/{id}/mark-unread | POST | Mark as unread |

---

## Route Registration

```php
// routes/web.php

use App\Http\Controllers\ContactController;

Route::get('contact', [ContactController::class, 'show'])
    ->name('contact');

Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');
```

---

## Rate Limiting Configuration

```php
// app/Providers/RouteServiceProvider.php or bootstrap/app.php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('contact', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

---

## Form Structure

### HTML Form

```html
<form action="/contact" method="POST">
    <input type="hidden" name="_token" value="...">

    <!-- Honeypot (hidden) -->
    <div style="display: none;">
        <input type="text" name="website" autocomplete="off">
    </div>

    <input type="text" name="name" required>
    <input type="email" name="email" required>
    <input type="text" name="subject" required>
    <textarea name="message" required minlength="10" maxlength="5000"></textarea>

    <button type="submit">Send Message</button>
</form>
```

---

## Response Formats

### Success Flash Message

```php
session()->flash('success', 'Thank you for your message! We\'ll get back to you soon.');
```

### Validation Error Response

```php
// Automatic redirect with errors
return back()
    ->withErrors($validator)
    ->withInput();
```

### Rate Limit Response (429)

```json
{
    "message": "Too Many Attempts.",
    "retry_after": 60
}
```

---

## Security Headers

Recommended headers for contact page:

```php
// In middleware
$response->headers->set('X-Frame-Options', 'DENY');
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('X-XSS-Protection', '1; mode=block');
```

---

## Email Notification Flow

```
[Form Submit] → [Validation] → [Save to DB] → [Queue Email] → [Send]
                     │                              │
                     ▼                              ▼
              [Validation Error]             [Log Failure]
              [Redirect w/ Errors]           [Message Still Saved]
```

---

## Middleware Stack

| Order | Middleware | Purpose |
|-------|------------|---------|
| 1 | web | Session, CSRF |
| 2 | throttle:5,1 | Rate limiting |
| 3 | (controller) | Business logic |

---

## API Endpoint (Optional)

For headless/SPA implementations:

### POST /api/contact

**Headers**:
```
Content-Type: application/json
Accept: application/json
```

**Request**:
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "subject": "Inquiry",
    "message": "Hello, I would like to know..."
}
```

**Response (200)**:
```json
{
    "success": true,
    "message": "Thank you for your message!"
}
```

**Response (422)**:
```json
{
    "success": false,
    "errors": {
        "email": ["Please enter a valid email address."]
    }
}
```

**Response (429)**:
```json
{
    "success": false,
    "message": "Too many attempts. Please try again later.",
    "retry_after": 60
}
```
