# Route Contracts: Newsletter

## Public Routes

### POST /newsletter/subscribe

**Purpose**: Subscribe to newsletter

**Controller**: `SubscriptionController@store`

**Request Body**:
```json
{
    "email": "user@example.com",
    "name": "John Doe"  // Optional
}
```

**Validation**:
- `email`: required, valid email format

**Response**:
- Success: Redirect with flash message "Please check your email to confirm subscription."
- Already subscribed: Redirect with flash message "You are already subscribed!"
- Validation error: Redirect with errors

---

### GET /newsletter/verify/{subscriber}

**Purpose**: Verify email subscription (double opt-in)

**Controller**: `SubscriptionController@verify`

**Parameters**:
- `subscriber`: Subscriber ID
- `signature`: Signed URL signature (auto-validated)
- `expires`: URL expiration timestamp

**Response**:
- Success (200): View `newsletter.verified`
- Invalid/Expired (401): Error page

**Signed URL Example**:
```
/newsletter/verify/123?expires=1700000000&signature=abc123...
```

---

### GET /newsletter/unsubscribe/{token}

**Purpose**: Show unsubscribe confirmation page

**Controller**: `UnsubscribeController@show`

**Parameters**:
- `token`: Unsubscribe token (64 characters)

**Response**:
- Success (200): View `newsletter.unsubscribe`
- Invalid token (404): Not found

---

### POST /newsletter/unsubscribe/{token}

**Purpose**: Process unsubscription

**Controller**: `UnsubscribeController@unsubscribe`

**Parameters**:
- `token`: Unsubscribe token

**Response**:
- Success: Redirect to confirmation page
- Invalid token (404): Not found

---

## Admin Routes (Filament)

### Subscribers Management

| Route | Method | Description |
|-------|--------|-------------|
| /admin/subscribers | GET | List all subscribers |
| /admin/subscribers/{id} | GET | View subscriber |
| /admin/subscribers/{id}/edit | GET | Edit subscriber |
| /admin/subscribers/{id} | DELETE | Delete subscriber |

### Newsletter Management

| Route | Method | Description |
|-------|--------|-------------|
| /admin/newsletters | GET | List all newsletters |
| /admin/newsletters/create | GET | Create newsletter |
| /admin/newsletters/{id} | GET | View newsletter |
| /admin/newsletters/{id}/edit | GET | Edit newsletter |
| /admin/newsletters/{id} | DELETE | Delete newsletter |
| /admin/newsletters/{id}/send | POST | Send newsletter |
| /admin/newsletters/{id}/preview | GET | Preview newsletter |

---

## Route Registration

```php
// routes/web.php

// Public newsletter routes
Route::prefix('newsletter')->name('newsletter.')->group(function () {
    Route::post('subscribe', [SubscriptionController::class, 'store'])
        ->name('subscribe');

    Route::get('verify/{subscriber}', [SubscriptionController::class, 'verify'])
        ->name('verify')
        ->middleware('signed');

    Route::get('unsubscribe/{token}', [UnsubscribeController::class, 'show'])
        ->name('unsubscribe');

    Route::post('unsubscribe/{token}', [UnsubscribeController::class, 'unsubscribe'])
        ->name('unsubscribe.confirm');
});
```

---

## Email Headers

### List-Unsubscribe Header

All newsletter emails include:

```
List-Unsubscribe: <https://example.com/newsletter/unsubscribe/abc123>
List-Unsubscribe-Post: List-Unsubscribe=One-Click
```

---

## Rate Limiting

```php
// routes/web.php or RouteServiceProvider

Route::post('newsletter/subscribe', [SubscriptionController::class, 'store'])
    ->middleware('throttle:5,1')  // 5 attempts per minute
    ->name('newsletter.subscribe');
```

---

## API Endpoints (Optional)

For headless/SPA implementations:

### POST /api/newsletter/subscribe

**Request**:
```json
{
    "email": "user@example.com"
}
```

**Response (200)**:
```json
{
    "success": true,
    "message": "Please check your email to confirm subscription."
}
```

**Response (422)**:
```json
{
    "success": false,
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

---

## Webhook Endpoints (For Email Provider Integration)

### POST /webhooks/email/bounce

**Purpose**: Handle email bounces from provider

**Headers**:
- Authorization: Bearer {webhook_secret}

**Body** (varies by provider):
```json
{
    "email": "bounced@example.com",
    "type": "hard_bounce",
    "timestamp": "2025-01-15T10:30:00Z"
}
```

**Action**: Mark subscriber as bounced, stop sending

---

## Middleware

| Route Group | Middleware |
|-------------|------------|
| Public subscribe | `web`, `throttle:5,1` |
| Verify | `web`, `signed` |
| Unsubscribe | `web` |
| Admin | `web`, `auth`, `filament` |
| Webhooks | `api`, custom signature verification |
