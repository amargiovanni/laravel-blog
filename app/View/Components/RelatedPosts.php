<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\Post;
use App\Services\RelatedPostsService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class RelatedPosts extends Component
{
    /**
     * The related posts.
     *
     * @var Collection<int, Post>
     */
    public Collection $relatedPosts;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public Post $post,
        public int $limit = 4,
    ) {
        $service = app(RelatedPostsService::class);
        $this->relatedPosts = $service->getRelatedPosts($post, $limit);
    }

    /**
     * Determine if the component should be rendered.
     */
    public function shouldRender(): bool
    {
        return $this->relatedPosts->isNotEmpty();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.related-posts');
    }
}
