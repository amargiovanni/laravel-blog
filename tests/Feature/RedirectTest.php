<?php

declare(strict_types=1);

use App\Models\Redirect;
use App\Rules\NoRedirectLoop;
use App\Rules\NotSelfRedirect;
use Illuminate\Support\Facades\Cache;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Redirect Model', function () {
    test('redirect can be created', function () {
        $redirect = Redirect::factory()->create([
            'source_url' => '/old-page',
            'target_url' => '/new-page',
            'status_code' => 301,
        ]);

        expect($redirect)->toBeInstanceOf(Redirect::class);
        expect($redirect->source_url)->toBe('/old-page');
        expect($redirect->target_url)->toBe('/new-page');
        expect($redirect->status_code)->toBe(301);
    });

    test('redirect can be permanent (301)', function () {
        $redirect = Redirect::factory()->permanent()->create();

        expect($redirect->status_code)->toBe(301);
    });

    test('redirect can be temporary (302)', function () {
        $redirect = Redirect::factory()->temporary()->create();

        expect($redirect->status_code)->toBe(302);
    });

    test('redirect can be inactive', function () {
        $redirect = Redirect::factory()->inactive()->create();

        expect($redirect->is_active)->toBeFalse();
    });

    test('redirect can be automatic', function () {
        $redirect = Redirect::factory()->automatic()->create();

        expect($redirect->is_automatic)->toBeTrue();
    });

    test('redirect can record hits', function () {
        $redirect = Redirect::factory()->create(['hits' => 0]);

        expect($redirect->hits)->toBe(0);
        expect($redirect->last_hit_at)->toBeNull();

        $redirect->recordHit();
        $redirect->refresh();

        expect($redirect->hits)->toBe(1);
        expect($redirect->last_hit_at)->not->toBeNull();
    });
})->group('redirect');

describe('Redirect Scopes', function () {
    test('active scope returns only active redirects', function () {
        Redirect::factory()->count(3)->create(['is_active' => true]);
        Redirect::factory()->count(2)->inactive()->create();

        expect(Redirect::active()->count())->toBe(3);
    });

    test('automatic scope returns only automatic redirects', function () {
        Redirect::factory()->count(2)->automatic()->create();
        Redirect::factory()->count(3)->create(['is_automatic' => false]);

        expect(Redirect::automatic()->count())->toBe(2);
    });

    test('manual scope returns only manual redirects', function () {
        Redirect::factory()->count(2)->automatic()->create();
        Redirect::factory()->count(3)->create(['is_automatic' => false]);

        expect(Redirect::manual()->count())->toBe(3);
    });
})->group('redirect');

describe('Redirect Caching', function () {
    test('getCachedRedirects returns active redirects', function () {
        Redirect::factory()->create([
            'source_url' => '/cached-source',
            'target_url' => '/cached-target',
            'status_code' => 301,
            'is_active' => true,
        ]);

        Redirect::factory()->inactive()->create([
            'source_url' => '/inactive-source',
            'target_url' => '/inactive-target',
        ]);

        $cached = Redirect::getCachedRedirects();

        expect($cached)->toHaveKey('/cached-source');
        expect($cached)->not->toHaveKey('/inactive-source');
    });

    test('cache is cleared when redirect is saved', function () {
        // Create a redirect, which triggers cache clear on create
        $redirect = Redirect::factory()->create();

        // Manually set the cache
        Cache::put('redirects:all_active', ['test' => 'value'], 3600);

        // Update the redirect
        $redirect->update(['target_url' => '/updated-target']);

        // Cache should have been cleared
        expect(Cache::has('redirects:all_active'))->toBeFalse();
    });

    test('cache is cleared when redirect is deleted', function () {
        $redirect = Redirect::factory()->create();

        // Manually set the cache
        Cache::put('redirects:all_active', ['test' => 'value'], 3600);

        // Delete the redirect
        $redirect->delete();

        // Cache should have been cleared
        expect(Cache::has('redirects:all_active'))->toBeFalse();
    });
})->group('redirect');

describe('Loop Detection', function () {
    test('detects direct self-redirect', function () {
        $redirect = new Redirect([
            'source_url' => '/page-a',
            'target_url' => '/page-a',
        ]);

        expect($redirect->wouldCreateLoop())->toBeTrue();
    });

    test('detects indirect loop (A→B→A)', function () {
        // Create existing redirect: /page-b → /page-a
        Redirect::factory()->create([
            'source_url' => '/page-b',
            'target_url' => '/page-a',
            'is_active' => true,
        ]);

        // New redirect: /page-a → /page-b would create loop
        $redirect = new Redirect([
            'source_url' => '/page-a',
            'target_url' => '/page-b',
        ]);

        expect($redirect->wouldCreateLoop())->toBeTrue();
    });

    test('detects chain loop (A→B→C→A)', function () {
        // Create chain: /page-b → /page-c, /page-c → /page-a
        Redirect::factory()->create([
            'source_url' => '/page-b',
            'target_url' => '/page-c',
            'is_active' => true,
        ]);
        Redirect::factory()->create([
            'source_url' => '/page-c',
            'target_url' => '/page-a',
            'is_active' => true,
        ]);

        // New redirect: /page-a → /page-b would create loop
        $redirect = new Redirect([
            'source_url' => '/page-a',
            'target_url' => '/page-b',
        ]);

        expect($redirect->wouldCreateLoop())->toBeTrue();
    });

    test('allows valid non-loop redirects', function () {
        $redirect = new Redirect([
            'source_url' => '/old-page',
            'target_url' => '/new-page',
        ]);

        expect($redirect->wouldCreateLoop())->toBeFalse();
    });

    test('allows chain without loop', function () {
        Redirect::factory()->create([
            'source_url' => '/page-b',
            'target_url' => '/page-c',
            'is_active' => true,
        ]);

        $redirect = new Redirect([
            'source_url' => '/page-a',
            'target_url' => '/page-b',
        ]);

        expect($redirect->wouldCreateLoop())->toBeFalse();
    });

    test('loop detection normalizes URLs', function () {
        $redirect = new Redirect([
            'source_url' => '/PAGE-A/',
            'target_url' => '/page-a',
        ]);

        expect($redirect->wouldCreateLoop())->toBeTrue();
    });
})->group('redirect');

describe('Redirect Middleware', function () {
    test('redirects to target URL with 301', function () {
        Redirect::factory()->create([
            'source_url' => '/old-path',
            'target_url' => '/new-path',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $response = $this->get('/old-path');

        $response->assertRedirect('/new-path');
        $response->assertStatus(301);
    });

    test('redirects to target URL with 302', function () {
        Redirect::factory()->create([
            'source_url' => '/temp-old',
            'target_url' => '/temp-new',
            'status_code' => 302,
            'is_active' => true,
        ]);

        $response = $this->get('/temp-old');

        $response->assertRedirect('/temp-new');
        $response->assertStatus(302);
    });

    test('inactive redirects are not executed', function () {
        Redirect::factory()->inactive()->create([
            'source_url' => '/inactive-path',
            'target_url' => '/should-not-redirect',
        ]);

        $response = $this->get('/inactive-path');

        $response->assertStatus(404);
    });

    test('preserves query string on redirect', function () {
        Redirect::factory()->create([
            'source_url' => '/old-path',
            'target_url' => '/new-path',
            'is_active' => true,
        ]);

        $response = $this->get('/old-path?foo=bar');

        $response->assertRedirect('/new-path?foo=bar');
    });

    test('records hit on redirect', function () {
        $redirect = Redirect::factory()->create([
            'source_url' => '/tracked-path',
            'target_url' => '/destination',
            'hits' => 0,
            'is_active' => true,
        ]);

        $this->get('/tracked-path');

        $redirect->refresh();
        expect($redirect->hits)->toBe(1);
        expect($redirect->last_hit_at)->not->toBeNull();
    });

    test('does not redirect POST requests', function () {
        Redirect::factory()->create([
            'source_url' => '/form-path',
            'target_url' => '/new-form-path',
            'is_active' => true,
        ]);

        $response = $this->post('/form-path');

        // POST to unknown route returns 405 (Method Not Allowed) or 404
        // The important thing is it doesn't redirect (302/301)
        expect($response->status())->not->toBe(301);
        expect($response->status())->not->toBe(302);
    });

    test('non-matching paths pass through', function () {
        Redirect::factory()->create([
            'source_url' => '/specific-path',
            'target_url' => '/new-path',
            'is_active' => true,
        ]);

        $response = $this->get('/different-path');

        $response->assertStatus(404);
    });
})->group('redirect');

describe('NotSelfRedirect Validation Rule', function () {
    test('fails when source equals target', function () {
        $rule = new NotSelfRedirect('/page-a');
        $failed = false;

        $rule->validate('target_url', '/page-a', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeTrue();
    });

    test('passes when source differs from target', function () {
        $rule = new NotSelfRedirect('/page-a');
        $failed = false;

        $rule->validate('target_url', '/page-b', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeFalse();
    });

    test('normalizes URLs for comparison', function () {
        $rule = new NotSelfRedirect('/page-a/');
        $failed = false;

        $rule->validate('target_url', '/PAGE-A', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeTrue();
    });

    test('handles null source URL', function () {
        $rule = new NotSelfRedirect(null);
        $failed = false;

        $rule->validate('target_url', '/page-a', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeFalse();
    });
})->group('redirect');

describe('NoRedirectLoop Validation Rule', function () {
    test('fails when redirect would create loop', function () {
        Redirect::factory()->create([
            'source_url' => '/page-b',
            'target_url' => '/page-a',
            'is_active' => true,
        ]);

        $rule = new NoRedirectLoop('/page-a');
        $failed = false;

        $rule->validate('target_url', '/page-b', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeTrue();
    });

    test('passes when no loop would be created', function () {
        $rule = new NoRedirectLoop('/page-a');
        $failed = false;

        $rule->validate('target_url', '/page-b', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeFalse();
    });

    test('handles null source URL', function () {
        $rule = new NoRedirectLoop(null);
        $failed = false;

        $rule->validate('target_url', '/page-a', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeFalse();
    });

    test('excludes current redirect when editing', function () {
        $existing = Redirect::factory()->create([
            'source_url' => '/page-a',
            'target_url' => '/page-b',
            'is_active' => true,
        ]);

        // When editing, changing target should be allowed
        $rule = new NoRedirectLoop('/page-a', $existing->id);
        $failed = false;

        $rule->validate('target_url', '/page-c', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeFalse();
    });
})->group('redirect');

describe('Redirect findBySourceUrl', function () {
    test('finds redirect by exact source URL', function () {
        $redirect = Redirect::factory()->create([
            'source_url' => '/find-me',
            'target_url' => '/found',
            'is_active' => true,
        ]);

        $found = Redirect::findBySourceUrl('/find-me');

        expect($found)->not->toBeNull();
        expect($found->id)->toBe($redirect->id);
    });

    test('returns null for non-existent source', function () {
        $found = Redirect::findBySourceUrl('/does-not-exist');

        expect($found)->toBeNull();
    });

    test('does not find inactive redirects', function () {
        Redirect::factory()->inactive()->create([
            'source_url' => '/inactive-find',
            'target_url' => '/somewhere',
        ]);

        $found = Redirect::findBySourceUrl('/inactive-find');

        expect($found)->toBeNull();
    });

    test('normalizes URL when searching', function () {
        Redirect::factory()->create([
            'source_url' => '/normalized',
            'target_url' => '/destination',
            'is_active' => true,
        ]);

        $found = Redirect::findBySourceUrl('normalized/');

        expect($found)->not->toBeNull();
    });
})->group('redirect');

describe('Redirect Factory States', function () {
    test('withHits state sets hits and last_hit_at', function () {
        $redirect = Redirect::factory()->withHits(25)->create();

        expect($redirect->hits)->toBe(25);
        expect($redirect->last_hit_at)->not->toBeNull();
    });

    test('factory creates unique source URLs', function () {
        $redirects = Redirect::factory()->count(5)->create();

        $sourceUrls = $redirects->pluck('source_url')->toArray();
        $uniqueUrls = array_unique($sourceUrls);

        expect(count($uniqueUrls))->toBe(5);
    });
})->group('redirect');
