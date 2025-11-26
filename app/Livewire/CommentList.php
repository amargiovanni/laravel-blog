<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class CommentList extends Component
{
    public Post $post;

    public ?int $replyingTo = null;

    /**
     * @var Collection<int, Comment>
     */
    public Collection $comments;

    public function mount(Post $post): void
    {
        $this->post = $post;
        $this->loadComments();
    }

    #[On('comment-submitted')]
    public function loadComments(): void
    {
        $maxDepth = config('comments.max_depth', 3);

        $this->comments = $this->post
            ->approvedComments()
            ->with(['approvedReplies' => function ($query) use ($maxDepth): void {
                // Load nested replies recursively
                $this->loadNestedReplies($query, $maxDepth - 1);
            }])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function startReply(int $commentId): void
    {
        $this->replyingTo = $commentId;
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
    }

    public function render(): View
    {
        return view('livewire.comment-list');
    }

    /**
     * Recursively load nested replies up to max depth.
     *
     * @param  mixed  $query
     */
    protected function loadNestedReplies($query, int $remainingDepth): void
    {
        if ($remainingDepth <= 0) {
            return;
        }

        $query->with(['approvedReplies' => function ($subQuery) use ($remainingDepth): void {
            $this->loadNestedReplies($subQuery, $remainingDepth - 1);
        }]);
    }
}
