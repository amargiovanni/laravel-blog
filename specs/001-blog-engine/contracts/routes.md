# Route Contracts: Laravel Blog Engine

**Feature**: 001-blog-engine
**Date**: 2025-11-25

This document defines the web routes and their contracts for the Laravel Blog Engine.

---

## Public Routes (Frontend)

### Posts

| Method | URI | Name | Controller/Component | Description |
|--------|-----|------|---------------------|-------------|
| GET | `/` | home | `pages.home` | Blog homepage with latest posts |
| GET | `/posts` | posts.index | `pages.post.index` | Paginated post listing |
| GET | `/posts/{slug}` | posts.show | `pages.post.show` | Single post view |

### Categories

| Method | URI | Name | Controller/Component | Description |
|--------|-----|------|---------------------|-------------|
| GET | `/category/{slug}` | categories.show | `pages.category.show` | Posts by category |

### Tags

| Method | URI | Name | Controller/Component | Description |
|--------|-----|------|---------------------|-------------|
| GET | `/tag/{slug}` | tags.show | `pages.tag.show` | Posts by tag |

### Search

| Method | URI | Name | Controller/Component | Description |
|--------|-----|------|---------------------|-------------|
| GET | `/search` | search | `pages.search` | Search results page |
| GET | `/search/suggest` | search.suggest | `SearchController@suggest` | Autocomplete suggestions (JSON) |

### Comments (Livewire Actions)

| Method | URI | Name | Component Action | Description |
|--------|-----|------|-----------------|-------------|
| POST | (Livewire) | - | `Comments::submit` | Submit new comment |

### Feeds

| Method | URI | Name | Controller | Description |
|--------|-----|------|------------|-------------|
| GET | `/feed` | feeds.main | `FeedController@index` | Main RSS feed |
| GET | `/feed/atom` | feeds.atom | `FeedController@atom` | Atom feed |
| GET | `/category/{slug}/feed` | feeds.category | `FeedController@category` | Category RSS feed |

### SEO

| Method | URI | Name | Controller | Description |
|--------|-----|------|------------|-------------|
| GET | `/sitemap.xml` | sitemap | `SitemapController@index` | XML sitemap |
| GET | `/robots.txt` | robots | `SeoController@robots` | Robots.txt |

---

## Authentication Routes

Using Laravel's built-in auth with Livewire Volt pages.

| Method | URI | Name | Component | Description |
|--------|-----|------|-----------|-------------|
| GET | `/login` | login | `pages.auth.login` | Login form |
| POST | (Livewire) | - | `Login::authenticate` | Process login |
| POST | `/logout` | logout | `LogoutController` | Logout action |
| GET | `/register` | register | `pages.auth.register` | Registration form |
| POST | (Livewire) | - | `Register::create` | Process registration |
| GET | `/forgot-password` | password.request | `pages.auth.forgot-password` | Password reset request |
| POST | (Livewire) | - | `ForgotPassword::send` | Send reset email |
| GET | `/reset-password/{token}` | password.reset | `pages.auth.reset-password` | Password reset form |
| POST | (Livewire) | - | `ResetPassword::reset` | Process password reset |
| GET | `/verify-email` | verification.notice | `pages.auth.verify-email` | Email verification notice |
| GET | `/verify-email/{id}/{hash}` | verification.verify | `VerifyEmailController` | Verify email |

---

## Admin Routes (Filament)

All admin routes are handled by Filament v4 under `/admin` prefix.

### Filament Resources

| Resource | URI Pattern | Description |
|----------|-------------|-------------|
| PostResource | `/admin/posts/*` | Post CRUD |
| CategoryResource | `/admin/categories/*` | Category CRUD |
| TagResource | `/admin/tags/*` | Tag CRUD |
| CommentResource | `/admin/comments/*` | Comment moderation |
| UserResource | `/admin/users/*` | User management |
| MediaResource | `/admin/media/*` | Media library |

### Filament Pages

| Page | URI | Description |
|------|-----|-------------|
| Dashboard | `/admin` | Analytics dashboard |
| ActivityLog | `/admin/activity-log` | Activity log viewer |
| Settings | `/admin/settings` | Site settings |
| ThemeSettings | `/admin/theme-settings` | Theme configuration |

---

## Route Parameters

### Post Slug

```php
Route::get('/posts/{post:slug}', ...);
// Implicit route model binding with slug
```

### Category Slug

```php
Route::get('/category/{category:slug}', ...);
// Implicit route model binding with slug
// Includes subcategory posts
```

### Tag Slug

```php
Route::get('/tag/{tag:slug}', ...);
// Implicit route model binding with slug
```

---

## Query Parameters

### Pagination

All list routes support:
- `page` (int): Page number, default 1
- `per_page` (int): Items per page, default 10, max 50

### Search

`/search` supports:
- `q` (string): Search query, required
- `category` (string): Filter by category slug
- `tag` (string): Filter by tag slug
- `author` (int): Filter by author ID
- `from` (date): Filter posts from date
- `to` (date): Filter posts to date

### Sorting

Post listings support:
- `sort` (string): 'newest' (default), 'oldest', 'popular'

---

## Response Formats

### HTML Pages (Livewire)

Standard HTML response with Livewire components.

### JSON Endpoints

#### Search Suggestions

```
GET /search/suggest?q=laravel
```

Response:
```json
{
  "suggestions": [
    {"title": "Laravel Tips", "slug": "laravel-tips", "type": "post"},
    {"name": "Laravel", "slug": "laravel", "type": "tag"}
  ]
}
```

### Feed Endpoints

RSS 2.0 and Atom XML formats via spatie/laravel-feed.

### Sitemap

XML sitemap following sitemap protocol.

---

## Middleware

### Public Routes

- `web` - Session, CSRF, etc.
- `throttle:60,1` - Rate limiting (60 requests/minute)

### Auth Routes

- `web`
- `guest` - Redirect if authenticated (login, register)
- `auth` - Require authentication (logout)

### Admin Routes (Filament)

- `web`
- `auth`
- `verified` - Email verification required
- `filament.auth` - Filament authentication
- Role-based via Filament's authorization

---

## Route Groups

```php
// routes/web.php

// Public routes
Route::get('/', HomePage::class)->name('home');

Route::prefix('posts')->group(function () {
    Route::get('/', PostIndex::class)->name('posts.index');
    Route::get('/{post:slug}', PostShow::class)->name('posts.show');
});

Route::get('/category/{category:slug}', CategoryShow::class)->name('categories.show');
Route::get('/tag/{tag:slug}', TagShow::class)->name('tags.show');

Route::prefix('search')->group(function () {
    Route::get('/', SearchPage::class)->name('search');
    Route::get('/suggest', [SearchController::class, 'suggest'])->name('search.suggest');
});

// Auth routes (via Fortify or custom)
Route::middleware('guest')->group(function () {
    Route::get('/login', LoginPage::class)->name('login');
    Route::get('/register', RegisterPage::class)->name('register');
    Route::get('/forgot-password', ForgotPasswordPage::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPasswordPage::class)->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');
    Route::get('/verify-email', VerifyEmailPage::class)->name('verification.notice');
});

// Feeds & SEO (handled by packages or controllers)
```

---

## Authorization Summary

| Route | Auth Required | Roles Allowed |
|-------|---------------|---------------|
| Public routes | No | All |
| Auth routes | Varies | All |
| `/admin/*` | Yes | admin, editor, author |
| Post creation | Yes | admin, editor, author |
| Post edit (own) | Yes | author (own posts) |
| Post edit (all) | Yes | admin, editor |
| Comment moderation | Yes | admin, editor |
| User management | Yes | admin |
| Settings | Yes | admin |
