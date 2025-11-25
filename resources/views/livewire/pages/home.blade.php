<?php

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.blog')]
#[Title('Home')]
class extends Component {
    public function with(): array
    {
        return [
            'posts' => Post::published()
                ->with(['author', 'categories', 'featuredImage'])
                ->latest('published_at')
                ->take(config('blog.posts.per_page', 10))
                ->get(),
        ];
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
    {{-- Hero Section --}}
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
            {{ config('blog.name', config('app.name')) }}
        </h1>
        <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">
            {{ config('blog.description') }}
        </p>
    </div>

    {{-- Posts Grid --}}
    @if($posts->isEmpty())
        <div class="text-center py-12">
            <p class="text-zinc-500 dark:text-zinc-400">{{ __('No posts yet. Check back soon!') }}</p>
        </div>
    @else
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($posts as $post)
                <article class="group flex flex-col bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:shadow-md transition-shadow">
                    {{-- Featured Image --}}
                    @if($post->featuredImage)
                        <a href="{{ route('posts.show', $post->slug) }}" wire:navigate class="aspect-video overflow-hidden">
                            <img
                                src="{{ $post->featuredImage->url }}"
                                alt="{{ $post->title }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                        </a>
                    @endif

                    <div class="flex-1 p-6">
                        {{-- Categories --}}
                        @if($post->categories->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach($post->categories->take(2) as $category)
                                    <a href="{{ route('categories.show', $category->slug) }}" wire:navigate class="text-xs font-medium text-accent hover:underline">
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
                        <p class="text-zinc-600 dark:text-zinc-400 text-sm mb-4 line-clamp-3">
                            {{ $post->excerpt }}
                        </p>

                        {{-- Meta --}}
                        <div class="flex items-center justify-between text-sm text-zinc-500 dark:text-zinc-400 mt-auto">
                            <span>{{ $post->author->name }}</span>
                            <time datetime="{{ $post->published_at->toIso8601String() }}">
                                {{ $post->published_at->format('M j, Y') }}
                            </time>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- View All Link --}}
        <div class="mt-12 text-center">
            <a href="{{ route('posts.index') }}" wire:navigate class="inline-flex items-center gap-2 text-accent font-medium hover:underline">
                {{ __('View all posts') }}
                <flux:icon.arrow-right class="size-4" />
            </a>
        </div>
    @endif
</div>
