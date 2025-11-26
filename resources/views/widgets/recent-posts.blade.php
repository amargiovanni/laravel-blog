<div class="widget widget-recent-posts">
    @if($title)
        <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-900 dark:text-white mb-4">{{ $title }}</h3>
    @endif

    @if($posts->isEmpty())
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No posts yet.') }}</p>
    @else
        <div class="space-y-4">
            @foreach($posts as $post)
                <article class="group flex gap-4">
                    @if($showThumbnail && $post->featuredImage)
                        <a href="{{ route('posts.show', $post->slug) }}" class="flex-shrink-0" wire:navigate>
                            <div class="w-16 h-16 rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                                <img
                                    src="{{ $post->featuredImage->url }}"
                                    alt="{{ $post->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                >
                            </div>
                        </a>
                    @endif
                    <div class="flex-1 min-w-0">
                        <a
                            href="{{ route('posts.show', $post->slug) }}"
                            class="text-sm font-medium text-zinc-900 dark:text-white hover:text-accent dark:hover:text-accent transition-colors line-clamp-2"
                            wire:navigate
                        >
                            {{ $post->title }}
                        </a>
                        @if($showDate)
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $post->published_at->format('M j, Y') }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
