<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\Comment as CommentModel;
use App\Models\Post;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Comment extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public CommentModel $comment,
        public Post $post,
        public ?int $replyingTo = null,
        public int $depth = 0,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.comment');
    }

    /**
     * Get max depth from config.
     */
    public function maxDepth(): int
    {
        return (int) config('comments.max_depth', 3);
    }

    /**
     * Check if replies are allowed at this depth.
     */
    public function canReply(): bool
    {
        return $this->depth < $this->maxDepth();
    }
}
