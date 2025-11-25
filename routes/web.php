<?php

declare(strict_types=1);

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Services\LlmsTxtService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// GEO: llms.txt route
Route::get('/llms.txt', function (LlmsTxtService $service) {
    $content = Cache::remember('llms.txt', now()->addHours(1), fn () => $service->generate());

    return response($content, 200, [
        'Content-Type' => 'text/markdown; charset=UTF-8',
    ]);
})->name('llms.txt');

// Blog routes
Volt::route('/', 'pages.home')->name('home');
Volt::route('/posts', 'pages.posts.index')->name('posts.index');
Volt::route('/posts/{slug}', 'pages.posts.show')->name('posts.show');
Volt::route('/categories', 'pages.categories.index')->name('categories.index');
Volt::route('/category/{slug}', 'pages.category')->name('categories.show');
Volt::route('/tags', 'pages.tags.index')->name('tags.index');
Volt::route('/tag/{slug}', 'pages.tag')->name('tags.show');
Volt::route('/archives', 'pages.archives')->name('archives');
Volt::route('/archives/{year}/{month?}', 'pages.archives-filter')->name('archives.filter');

// Static pages - must be last to catch remaining slugs
Volt::route('/{slug}', 'pages.page')->name('pages.show');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
