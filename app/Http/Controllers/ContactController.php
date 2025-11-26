<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Mail\ContactFormSubmission;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    /**
     * Display the contact form.
     */
    public function show(): View
    {
        return view('contact');
    }

    /**
     * Store a new contact message.
     */
    public function store(ContactFormRequest $request): RedirectResponse
    {
        // Check honeypot - silently reject spam
        if ($request->isSpam()) {
            Log::info('Contact form honeypot triggered', [
                'ip' => $request->ip(),
                'email' => $request->validated()['email'] ?? 'unknown',
            ]);

            // Return success to not reveal that spam was detected
            return back()->with('success', __('Thank you for your message. We will get back to you soon.'));
        }

        $validated = $request->validated();

        // Sanitize message content
        $validated['message'] = strip_tags($validated['message']);

        // Create the contact message
        $contactMessage = ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Send email notification
        $this->sendNotification($contactMessage);

        return back()->with('success', __('Thank you for your message. We will get back to you soon.'));
    }

    /**
     * Send notification email to admin.
     */
    protected function sendNotification(ContactMessage $contactMessage): void
    {
        try {
            $adminEmail = config('mail.admin_email', config('mail.from.address'));

            if ($adminEmail) {
                Mail::to($adminEmail)->send(new ContactFormSubmission($contactMessage));
            }
        } catch (Throwable $e) {
            Log::error('Failed to send contact form notification', [
                'contact_message_id' => $contactMessage->id,
                'error' => $e->getMessage(),
            ]);
            // Message is already saved, so don't throw - graceful degradation
        }
    }
}
