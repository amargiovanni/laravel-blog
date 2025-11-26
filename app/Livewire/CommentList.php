<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class CommentList extends Component
{
    public Post $post;

    public ?int $replyingTo = null;

    public function mount(Post $post): void
    {
        $this->post = $post;
    }

    #[On('comment-submitted')]
    public function refresh(): void
    {
        // This method triggers a re-render which will reload comments
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
        return view('livewire.comment-list', [
            'comments' => $this->getComments(),
        ]);
    }

    /**
     * Get comments with all nested replies loaded.
     *
     * @return Collection<int, \App\Models\Comment>
     */
    protected function getComments(): Collection
    {
        $maxDepth = config('comments.max_depth', 3);

        return $this->post
            ->approvedComments()
            ->with(['approvedReplies' => function ($query) use ($maxDepth): void {
                $this->loadNestedReplies($query, $maxDepth - 1);
            }])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Recursively load nested replies up to max depth.
     *
     * @param  mixed  $query
     */
    protected function loadNestedReplies($query, int $remainingDepth): void
    {
        if ($remainingDepth <= 0) {
            // Load empty relation to prevent lazy loading violation
            $query->with(['approvedReplies' => fn ($q) => $q->whereRaw('1 = 0')]);

            return;
        }

        $query->with(['approvedReplies' => function ($subQuery) use ($remainingDepth): void {
            $this->loadNestedReplies($subQuery, $remainingDepth - 1);
        }]);
    }
}
