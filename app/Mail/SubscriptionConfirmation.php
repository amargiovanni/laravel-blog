<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class SubscriptionConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $verificationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Subscriber $subscriber
    ) {
        $this->verificationUrl = URL::temporarySignedRoute(
            'newsletter.verify',
            now()->addHours(24),
            ['subscriber' => $subscriber->id]
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Confirm Your Newsletter Subscription'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-confirmation',
            with: [
                'subscriber' => $this->subscriber,
                'verificationUrl' => $this->verificationUrl,
                'blogName' => config('blog.name', config('app.name')),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
