<?php

declare(strict_types=1);

use App\Mail\SubscriptionConfirmation;
use App\Models\Subscriber;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Newsletter Subscription', function () {
    test('visitor can subscribe to newsletter', function () {
        Mail::fake();

        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('newsletter.subscribe'), [
                'email' => 'test@example.com',
                'name' => 'Test User',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subscribers', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        Mail::assertQueued(SubscriptionConfirmation::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    });

    test('subscriber receives confirmation email', function () {
        Mail::fake();

        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('newsletter.subscribe'), [
                'email' => 'confirm@example.com',
            ]);

        Mail::assertQueued(SubscriptionConfirmation::class, function ($mail) {
            return $mail->hasTo('confirm@example.com');
        });
    });

    test('subscriber email is required', function () {
        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('newsletter.subscribe'), [
                'email' => '',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('subscribers', ['email' => '']);
    });

    test('subscriber email must be valid', function () {
        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('newsletter.subscribe'), [
                'email' => 'not-an-email',
            ]);

        $response->assertSessionHasErrors('email');
    });

    test('already verified subscriber sees info message', function () {
        Mail::fake();

        $subscriber = Subscriber::factory()->verified()->create([
            'email' => 'verified@example.com',
        ]);

        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('newsletter.subscribe'), [
                'email' => 'verified@example.com',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('info');

        Mail::assertNothingQueued();
    });

    test('unverified subscriber receives new confirmation email', function () {
        Mail::fake();

        $subscriber = Subscriber::factory()->create([
            'email' => 'pending@example.com',
            'verified_at' => null,
        ]);

        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('newsletter.subscribe'), [
                'email' => 'pending@example.com',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Mail::assertQueued(SubscriptionConfirmation::class);
    });

    test('previously unsubscribed user can resubscribe', function () {
        Mail::fake();

        $subscriber = Subscriber::factory()->unsubscribed()->create([
            'email' => 'resubscribe@example.com',
        ]);

        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('newsletter.subscribe'), [
                'email' => 'resubscribe@example.com',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $subscriber->refresh();
        expect($subscriber->unsubscribed_at)->toBeNull();
        expect($subscriber->verified_at)->toBeNull();

        Mail::assertQueued(SubscriptionConfirmation::class);
    });

    test('subscription stores IP address', function () {
        Mail::fake();

        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('newsletter.subscribe'), [
                'email' => 'ip-test@example.com',
            ]);

        $subscriber = Subscriber::where('email', 'ip-test@example.com')->first();
        expect($subscriber->subscribed_ip)->not->toBeNull();
    });
})->group('newsletter');

describe('Newsletter Verification', function () {
    test('subscriber can verify subscription with valid signed URL', function () {
        $subscriber = Subscriber::factory()->create([
            'email' => 'verify@example.com',
            'verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'newsletter.verify',
            now()->addHours(24),
            ['subscriber' => $subscriber->id]
        );

        $response = $this->get($verificationUrl);

        $response->assertOk();
        $response->assertViewIs('newsletter.verified');

        $subscriber->refresh();
        expect($subscriber->verified_at)->not->toBeNull();
    });

    test('invalid signature shows expired view', function () {
        $subscriber = Subscriber::factory()->create([
            'verified_at' => null,
        ]);

        $response = $this->get(route('newsletter.verify', ['subscriber' => $subscriber->id]));

        $response->assertOk();
        $response->assertViewIs('newsletter.verification-expired');

        $subscriber->refresh();
        expect($subscriber->verified_at)->toBeNull();
    });

    test('expired link shows expired view', function () {
        $subscriber = Subscriber::factory()->create([
            'verified_at' => null,
        ]);

        $expiredUrl = URL::temporarySignedRoute(
            'newsletter.verify',
            now()->subHour(),
            ['subscriber' => $subscriber->id]
        );

        $response = $this->get($expiredUrl);

        $response->assertOk();
        $response->assertViewIs('newsletter.verification-expired');
    });

    test('already verified subscriber sees appropriate message', function () {
        $subscriber = Subscriber::factory()->verified()->create([
            'email' => 'already-verified@example.com',
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'newsletter.verify',
            now()->addHours(24),
            ['subscriber' => $subscriber->id]
        );

        $response = $this->get($verificationUrl);

        $response->assertOk();
        $response->assertViewIs('newsletter.already-verified');
    });
})->group('newsletter');

describe('Newsletter Unsubscribe', function () {
    test('subscriber can view unsubscribe page', function () {
        $subscriber = Subscriber::factory()->verified()->create();

        $response = $this->get(route('newsletter.unsubscribe', $subscriber->unsubscribe_token));

        $response->assertOk();
        $response->assertViewIs('newsletter.unsubscribe');
        $response->assertSee($subscriber->email);
    });

    test('subscriber can confirm unsubscription', function () {
        $subscriber = Subscriber::factory()->verified()->create([
            'email' => 'unsubscribe-me@example.com',
        ]);

        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('newsletter.unsubscribe.confirm', $subscriber->unsubscribe_token));

        $response->assertOk();
        $response->assertViewIs('newsletter.unsubscribed');

        $subscriber->refresh();
        expect($subscriber->unsubscribed_at)->not->toBeNull();
    });

    test('invalid token shows error page', function () {
        $response = $this->get(route('newsletter.unsubscribe', 'invalid-token-here'));

        $response->assertOk();
        $response->assertViewIs('newsletter.unsubscribe-invalid');
    });

    test('already unsubscribed user sees appropriate message', function () {
        $subscriber = Subscriber::factory()->unsubscribed()->create();

        $response = $this->get(route('newsletter.unsubscribe', $subscriber->unsubscribe_token));

        $response->assertOk();
        $response->assertViewIs('newsletter.already-unsubscribed');
    });

    test('each subscriber has unique unsubscribe token', function () {
        $subscriber1 = Subscriber::factory()->create();
        $subscriber2 = Subscriber::factory()->create();

        expect($subscriber1->unsubscribe_token)->not->toBe($subscriber2->unsubscribe_token);
    });
})->group('newsletter');

describe('Subscriber Model', function () {
    test('generates unique unsubscribe token on creation', function () {
        $subscriber = Subscriber::create([
            'email' => 'auto-token@example.com',
        ]);

        expect($subscriber->unsubscribe_token)->not->toBeNull();
        expect(strlen($subscriber->unsubscribe_token))->toBe(64);
    });

    test('isVerified returns correct status', function () {
        $verified = Subscriber::factory()->verified()->create();
        $unverified = Subscriber::factory()->create(['verified_at' => null]);

        expect($verified->isVerified())->toBeTrue();
        expect($unverified->isVerified())->toBeFalse();
    });

    test('isActive returns correct status', function () {
        $active = Subscriber::factory()->verified()->create();
        $unverified = Subscriber::factory()->create(['verified_at' => null]);
        $unsubscribed = Subscriber::factory()->unsubscribed()->create();

        expect($active->isActive())->toBeTrue();
        expect($unverified->isActive())->toBeFalse();
        expect($unsubscribed->isActive())->toBeFalse();
    });

    test('active scope returns only active subscribers', function () {
        Subscriber::factory()->verified()->count(3)->create();
        Subscriber::factory()->count(2)->create(['verified_at' => null]);
        Subscriber::factory()->unsubscribed()->count(2)->create();

        expect(Subscriber::active()->count())->toBe(3);
    });

    test('verified scope returns verified subscribers', function () {
        Subscriber::factory()->verified()->count(3)->create();
        Subscriber::factory()->count(2)->create(['verified_at' => null]);

        expect(Subscriber::verified()->count())->toBe(3);
    });

    test('unsubscribed scope returns unsubscribed subscribers', function () {
        Subscriber::factory()->verified()->count(3)->create();
        Subscriber::factory()->unsubscribed()->count(2)->create();

        expect(Subscriber::unsubscribed()->count())->toBe(2);
    });

    test('getUnsubscribeUrl generates correct URL', function () {
        $subscriber = Subscriber::factory()->create();

        $url = $subscriber->getUnsubscribeUrl();

        expect($url)->toContain($subscriber->unsubscribe_token);
        expect($url)->toContain('newsletter/unsubscribe');
    });
})->group('newsletter');

describe('Rate Limiting', function () {
    test('subscription endpoint is rate limited', function () {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->withoutMiddleware(ValidateCsrfToken::class)
                ->post(route('newsletter.subscribe'), [
                    'email' => "test{$i}@example.com",
                ]);
        }

        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('newsletter.subscribe'), [
                'email' => 'rate-limited@example.com',
            ]);

        $response->assertStatus(429);
    });
})->group('newsletter');
