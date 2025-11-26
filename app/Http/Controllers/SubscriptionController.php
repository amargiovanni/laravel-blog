<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionRequest;
use App\Mail\SubscriptionConfirmation;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * Store a new newsletter subscription.
     */
    public function store(SubscriptionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $existingSubscriber = Subscriber::where('email', $validated['email'])->first();

        if ($existingSubscriber) {
            if ($existingSubscriber->isVerified() && $existingSubscriber->isActive()) {
                return back()->with('info', __('You are already subscribed to our newsletter.'));
            }

            if ($existingSubscriber->unsubscribed_at !== null) {
                $existingSubscriber->update([
                    'unsubscribed_at' => null,
                    'verified_at' => null,
                    'name' => $validated['name'] ?? $existingSubscriber->name,
                    'subscribed_ip' => $request->ip(),
                ]);

                Mail::to($existingSubscriber->email)->send(new SubscriptionConfirmation($existingSubscriber));

                return back()->with('success', __('Welcome back! Please check your email to confirm your subscription.'));
            }

            if (! $existingSubscriber->isVerified()) {
                Mail::to($existingSubscriber->email)->send(new SubscriptionConfirmation($existingSubscriber));

                return back()->with('success', __('A confirmation email has been sent. Please check your inbox.'));
            }
        }

        $subscriber = Subscriber::create([
            'email' => $validated['email'],
            'name' => $validated['name'] ?? null,
            'subscribed_ip' => $request->ip(),
        ]);

        Mail::to($subscriber->email)->send(new SubscriptionConfirmation($subscriber));

        return back()->with('success', __('Thanks for subscribing! Please check your email to confirm your subscription.'));
    }

    /**
     * Verify a newsletter subscription.
     */
    public function verify(Request $request, Subscriber $subscriber): View|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return view('newsletter.verification-expired');
        }

        if ($subscriber->isVerified()) {
            return view('newsletter.already-verified', [
                'subscriber' => $subscriber,
            ]);
        }

        $subscriber->markAsVerified();

        return view('newsletter.verified', [
            'subscriber' => $subscriber,
        ]);
    }
}
