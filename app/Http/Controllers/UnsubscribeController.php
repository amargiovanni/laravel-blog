<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnsubscribeController extends Controller
{
    /**
     * Show the unsubscribe confirmation page.
     */
    public function show(string $token): View|RedirectResponse
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->first();

        if (! $subscriber) {
            return view('newsletter.unsubscribe-invalid');
        }

        if ($subscriber->unsubscribed_at !== null) {
            return view('newsletter.already-unsubscribed', [
                'subscriber' => $subscriber,
            ]);
        }

        return view('newsletter.unsubscribe', [
            'subscriber' => $subscriber,
            'token' => $token,
        ]);
    }

    /**
     * Process the unsubscription.
     */
    public function unsubscribe(Request $request, string $token): View|RedirectResponse
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->first();

        if (! $subscriber) {
            return view('newsletter.unsubscribe-invalid');
        }

        if ($subscriber->unsubscribed_at !== null) {
            return view('newsletter.already-unsubscribed', [
                'subscriber' => $subscriber,
            ]);
        }

        $subscriber->markAsUnsubscribed();

        return view('newsletter.unsubscribed', [
            'subscriber' => $subscriber,
        ]);
    }
}
