<?php

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('components.layouts.blog')]
#[Title('Blog')]
class extends Component {
    use WithPagination;

    public function with(): array
    {
        return [
            'posts' => Post::published()
                ->with(['author', 'categories', 'featuredImage'])
                ->latest('published_at')
                ->paginate(config('blog.posts.per_page', 10)),
        ];
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold">{{ __('Blog') }}</h1>
        <p class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('All posts') }}</p>
    </div>

    {{-- Posts List --}}
    @if($posts->isEmpty())
        <div class="text-center py-12">
            <p class="text-zinc-500 dark:text-zinc-400">{{ __('No posts found.') }}</p>
        </div>
    @else
        <div class="space-y-8">
            @foreach($posts as $post)
                <article class="flex flex-col md:flex-row gap-6 p-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    {{-- Featured Image --}}
                    @if($post->featuredImage)
                        <a href="{{ route('posts.show', $post->slug) }}" wire:navigate class="md:w-64 md:shrink-0">
                            <img
                                src="{{ $post->featuredImage->url }}"
                                alt="{{ $post->title }}"
                                class="w-full h-48 md:h-full object-cover rounded-md"
                            />
                        </a>
                    @endif

                    <div class="flex-1">
                        {{-- Categories --}}
                        @if($post->categories->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mb-2">
                                @foreach($post->categories as $category)
                                    <a href="{{ route('category.show', $category->slug) }}" wire:navigate class="text-xs font-medium text-accent hover:underline">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        {{-- Title --}}
                        <h2 class="text-xl font-semibold mb-2">
                            <a href="{{ route('posts.show', $post->slug) }}" wire:navigate class="hover:text-accent transition-colors">
                                {{ $post->title }}
                            </a>
                        </h2>

                        {{-- Excerpt --}}
                        <p class="text-zinc-600 dark:text-zinc-400 text-sm mb-4 line-clamp-2">
                            {{ $post->excerpt }}
                        </p>

                        {{-- Meta --}}
                        <div class="flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                            <span>{{ $post->author->name }}</span>
                            <span>&middot;</span>
                            <time datetime="{{ $post->published_at->toIso8601String() }}">
                                {{ $post->published_at->format('M j, Y') }}
                            </time>
                            @if($post->view_count > 0)
                                <span>&middot;</span>
                                <span>{{ number_format($post->view_count) }} {{ __('views') }}</span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    @endif
</div>
