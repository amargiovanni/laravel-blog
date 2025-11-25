<?php

use App\Models\Post;
use App\Services\JsonLdService;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.blog')]
class extends Component {
    public Post $post;

    public function mount(string $slug): void
    {
        $this->post = Post::published()
            ->with(['author', 'categories', 'tags', 'featuredImage'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment view count
        $this->post->increment('view_count');

        // Share post with layout for JSON-LD
        View::share('post', $this->post);
    }
}; ?>

<article class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-12">
    {{-- Header --}}
    <header class="mb-8">
        {{-- Categories --}}
        @if($post->categories->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($post->categories as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" wire:navigate class="text-sm font-medium text-accent hover:underline">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Title --}}
        <h1 class="text-4xl font-bold tracking-tight sm:text-5xl mb-4">
            {{ $post->title }}
        </h1>

        {{-- Meta --}}
        <div class="flex flex-wrap items-center gap-4 text-zinc-500 dark:text-zinc-400">
            <div class="flex items-center gap-2">
                @if($post->author->avatar)
                    <img src="{{ $post->author->avatar }}" alt="{{ $post->author->name }}" class="size-8 rounded-full" />
                @else
                    <div class="size-8 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                        <span class="text-sm font-medium">{{ substr($post->author->name, 0, 1) }}</span>
                    </div>
                @endif
                <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $post->author->name }}</span>
            </div>
            <span>&middot;</span>
            <time datetime="{{ $post->published_at->toIso8601String() }}">
                {{ $post->published_at->format('F j, Y') }}
            </time>
            <span>&middot;</span>
            <span>{{ number_format($post->view_count) }} {{ __('views') }}</span>
        </div>
    </header>

    {{-- Featured Image --}}
    @if($post->featuredImage)
        <figure class="mb-8 -mx-4 sm:-mx-6 lg:-mx-8">
            <img
                src="{{ $post->featuredImage->url }}"
                alt="{{ $post->title }}"
                class="w-full rounded-lg"
            />
        </figure>
    @endif

    {{-- Content --}}
    <div class="prose prose-lg dark:prose-invert max-w-none">
        {!! $post->content !!}
    </div>

    {{-- Tags --}}
    @if($post->tags->isNotEmpty())
        <footer class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-700">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Tags:') }}</span>
                @foreach($post->tags as $tag)
                    <a href="{{ route('tags.show', $tag->slug) }}" wire:navigate class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                        {{ $tag->name }}
                    </a>
                @endforeach
            </div>
        </footer>
    @endif

    {{-- Author Bio --}}
    @if($post->author->bio)
        <div class="mt-12 p-6 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
            <div class="flex items-start gap-4">
                @if($post->author->avatar)
                    <img src="{{ $post->author->avatar }}" alt="{{ $post->author->name }}" class="size-16 rounded-full" />
                @else
                    <div class="size-16 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                        <span class="text-xl font-medium">{{ substr($post->author->name, 0, 1) }}</span>
                    </div>
                @endif
                <div>
                    <h3 class="font-semibold">{{ $post->author->name }}</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm mt-1">
                        {{ $post->author->bio }}
                    </p>
                </div>
            </div>
        </div>
    @endif
</article>
