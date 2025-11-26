<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class CommentForm extends Component
{
    public Post $post;

    public ?int $parentId = null;

    public string $authorName = '';

    public string $authorEmail = '';

    public string $content = '';

    public bool $notifyReplies = false;

    public string $website = ''; // Honeypot

    public bool $submitted = false;

    public function mount(Post $post, ?int $parentId = null): void
    {
        $this->post = $post;
        $this->parentId = $parentId;

        // Auto-fill for logged-in users
        if (auth()->check()) {
            $user = auth()->user();
            $this->authorName = $user->name;
            $this->authorEmail = $user->email;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'authorName' => ['required', 'string', 'max:255'],
            'authorEmail' => ['required', 'email:rfc', 'max:255'],
            'content' => [
                'required',
                'string',
                'min:'.config('comments.min_length', 3),
                'max:'.config('comments.max_length', 2000),
            ],
            'notifyReplies' => ['boolean'],
            'website' => ['nullable', 'string'], // Honeypot
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'authorName.required' => __('Please enter your name.'),
            'authorEmail.required' => __('Please enter your email address.'),
            'authorEmail.email' => __('Please enter a valid email address.'),
            'content.required' => __('Please enter your comment.'),
            'content.min' => __('Your comment is too short.'),
            'content.max' => __('Your comment is too long. Maximum :max characters allowed.', [
                'max' => config('comments.max_length', 2000),
            ]),
        ];
    }

    public function submit(): void
    {
        // Check honeypot
        if (! empty($this->website)) {
            Log::info('Comment honeypot triggered', [
                'ip' => request()->ip(),
                'post_id' => $this->post->id,
            ]);

            // Fake success to not reveal spam detection
            $this->submitted = true;
            $this->reset(['authorName', 'authorEmail', 'content', 'notifyReplies', 'website']);

            return;
        }

        // Check rate limiting
        $key = 'comment-submit:'.request()->ip();
        $limit = config('comments.rate_limit', 5);

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $this->addError('content', __('Too many comments. Please wait a moment before trying again.'));

            return;
        }

        RateLimiter::hit($key, 60);

        $this->validate();

        // Sanitize content
        $sanitizedContent = strip_tags($this->content);

        // Determine initial status
        $status = $this->determineInitialStatus($sanitizedContent);

        Comment::create([
            'post_id' => $this->post->id,
            'parent_id' => $this->parentId,
            'user_id' => auth()->id(),
            'author_name' => $this->authorName,
            'author_email' => $this->authorEmail,
            'content' => $sanitizedContent,
            'status' => $status,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'is_notify_replies' => $this->notifyReplies,
        ]);

        $this->submitted = true;
        $this->reset(['authorName', 'authorEmail', 'content', 'notifyReplies']);

        // Re-populate for logged-in users
        if (auth()->check()) {
            $user = auth()->user();
            $this->authorName = $user->name;
            $this->authorEmail = $user->email;
        }

        $this->dispatch('comment-submitted');
    }

    public function render(): View
    {
        return view('livewire.comment-form');
    }

    /**
     * Determine the initial status for a comment.
     */
    protected function determineInitialStatus(string $content): string
    {
        // Auto-hold comments with links if configured
        if (config('comments.auto_hold_links', true) && $this->containsLinks($content)) {
            return Comment::STATUS_PENDING;
        }

        // Check moderation mode
        if (config('comments.require_moderation', true)) {
            return Comment::STATUS_PENDING;
        }

        return Comment::STATUS_APPROVED;
    }

    /**
     * Check if content contains links.
     */
    protected function containsLinks(string $content): bool
    {
        return (bool) preg_match('/https?:\/\/|www\./i', $content);
    }
}
