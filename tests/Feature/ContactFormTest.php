<?php

declare(strict_types=1);

use App\Mail\ContactFormSubmission;
use App\Models\ContactMessage;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Mail;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Contact Form Display', function () {
    test('contact page can be rendered', function () {
        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee(__('Contact Us'));
    });

    test('contact page displays form fields', function () {
        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="subject"', false);
        $response->assertSee('name="message"', false);
    });

    test('contact page has honeypot field', function () {
        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee('name="website"', false);
    });
})->group('contact');

describe('Contact Form Submission', function () {
    test('visitor can submit contact form', function () {
        Mail::fake();

        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('contact.store'), [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'subject' => 'Test Subject',
                'message' => 'This is a test message.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message.',
        ]);

        Mail::assertQueued(ContactFormSubmission::class);
    });

    test('submission stores IP address and user agent', function () {
        Mail::fake();

        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('contact.store'), [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'subject' => 'IP Test',
                'message' => 'Testing IP storage.',
            ]);

        $message = ContactMessage::where('email', 'jane@example.com')->first();
        expect($message->ip_address)->not->toBeNull();
    });

    test('message content is sanitized', function () {
        Mail::fake();

        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('contact.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'subject' => 'XSS Test',
                'message' => '<script>alert("xss")</script>Hello',
            ]);

        $message = ContactMessage::where('email', 'test@example.com')->first();
        expect($message->message)->not->toContain('<script>');
        expect($message->message)->toContain('Hello');
    });
})->group('contact');

describe('Contact Form Validation', function () {
    test('name is required', function () {
        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('contact.store'), [
                'name' => '',
                'email' => 'test@example.com',
                'subject' => 'Test',
                'message' => 'Test message',
            ]);

        $response->assertSessionHasErrors('name');
    });

    test('email is required', function () {
        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('contact.store'), [
                'name' => 'Test',
                'email' => '',
                'subject' => 'Test',
                'message' => 'Test message',
            ]);

        $response->assertSessionHasErrors('email');
    });

    test('email must be valid', function () {
        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('contact.store'), [
                'name' => 'Test',
                'email' => 'not-an-email',
                'subject' => 'Test',
                'message' => 'Test message',
            ]);

        $response->assertSessionHasErrors('email');
    });

    test('subject is required', function () {
        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('contact.store'), [
                'name' => 'Test',
                'email' => 'test@example.com',
                'subject' => '',
                'message' => 'Test message',
            ]);

        $response->assertSessionHasErrors('subject');
    });

    test('message is required', function () {
        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('contact.store'), [
                'name' => 'Test',
                'email' => 'test@example.com',
                'subject' => 'Test',
                'message' => '',
            ]);

        $response->assertSessionHasErrors('message');
    });

    test('message has maximum length', function () {
        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('contact.store'), [
                'name' => 'Test',
                'email' => 'test@example.com',
                'subject' => 'Test',
                'message' => str_repeat('a', 5001),
            ]);

        $response->assertSessionHasErrors('message');
    });
})->group('contact');

describe('Contact Form Spam Protection', function () {
    test('honeypot filled submissions are silently rejected', function () {
        Mail::fake();

        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('contact.store'), [
                'name' => 'Bot User',
                'email' => 'bot@example.com',
                'subject' => 'Spam Subject',
                'message' => 'Spam message',
                'website' => 'http://spam-site.com', // Honeypot filled
            ]);

        // Should appear successful to not reveal spam detection
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // But message should not be saved
        $this->assertDatabaseMissing('contact_messages', [
            'email' => 'bot@example.com',
        ]);

        // And no email should be sent
        Mail::assertNothingQueued();
    });

    test('empty honeypot allows submission', function () {
        Mail::fake();

        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('contact.store'), [
                'name' => 'Human User',
                'email' => 'human@example.com',
                'subject' => 'Real Subject',
                'message' => 'Real message',
                'website' => '', // Empty honeypot
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'human@example.com',
        ]);
    });

    test('rate limiting blocks excessive submissions', function () {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->withoutMiddleware(ValidateCsrfToken::class)
                ->post(route('contact.store'), [
                    'name' => "User $i",
                    'email' => "test{$i}@example.com",
                    'subject' => 'Test',
                    'message' => 'Test message',
                ]);
        }

        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('contact.store'), [
                'name' => 'Rate Limited User',
                'email' => 'rate-limited@example.com',
                'subject' => 'Test',
                'message' => 'Test message',
            ]);

        $response->assertStatus(429);
    });
})->group('contact');

describe('ContactMessage Model', function () {
    test('message can be marked as read', function () {
        $message = ContactMessage::factory()->unread()->create();

        expect($message->is_read)->toBeFalse();
        expect($message->read_at)->toBeNull();

        $message->markAsRead();

        expect($message->is_read)->toBeTrue();
        expect($message->read_at)->not->toBeNull();
    });

    test('message can be marked as unread', function () {
        $message = ContactMessage::factory()->read()->create();

        expect($message->is_read)->toBeTrue();
        expect($message->read_at)->not->toBeNull();

        $message->markAsUnread();

        expect($message->is_read)->toBeFalse();
        expect($message->read_at)->toBeNull();
    });

    test('isRead returns correct status', function () {
        $read = ContactMessage::factory()->read()->create();
        $unread = ContactMessage::factory()->unread()->create();

        expect($read->isRead())->toBeTrue();
        expect($unread->isRead())->toBeFalse();
    });

    test('unread scope returns unread messages', function () {
        ContactMessage::factory()->read()->count(3)->create();
        ContactMessage::factory()->unread()->count(2)->create();

        expect(ContactMessage::unread()->count())->toBe(2);
    });

    test('read scope returns read messages', function () {
        ContactMessage::factory()->read()->count(3)->create();
        ContactMessage::factory()->unread()->count(2)->create();

        expect(ContactMessage::read()->count())->toBe(3);
    });
})->group('contact');
