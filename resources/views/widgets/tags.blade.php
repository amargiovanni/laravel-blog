<div class="widget widget-tags">
    @if($title)
        <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-900 dark:text-white mb-4">{{ $title }}</h3>
    @endif

    @if($tags->isEmpty())
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No tags yet.') }}</p>
    @else
        <div class="flex flex-wrap gap-2">
            @foreach($tags as $tag)
                <a
                    href="{{ route('tags.show', $tag->slug) }}"
                    class="inline-flex items-center px-3 py-1.5 rounded-full font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-accent hover:text-white dark:hover:bg-accent transition-colors duration-200"
                    style="font-size: {{ $tag->size }}rem"
                    title="{{ $tag->posts_count }} {{ Str::plural('post', $tag->posts_count) }}"
                    wire:navigate
                >
                    #{{ $tag->name }}
                </a>
            @endforeach
        </div>
    @endif
</div>
