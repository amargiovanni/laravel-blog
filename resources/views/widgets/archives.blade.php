<div class="widget widget-archives bg-white dark:bg-zinc-800 rounded-lg p-5 border border-zinc-200 dark:border-zinc-700">
    @if($title)
        <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-900 dark:text-white mb-4">{{ $title }}</h3>
    @endif

    @if(empty($archives))
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No archives yet.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach($archives as $archive)
                <li>
                    <a
                        href="{{ $archive['url'] }}"
                        class="flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-400 hover:text-accent dark:hover:text-accent transition-colors"
                        wire:navigate
                    >
                        <span>{{ $archive['label'] }}</span>
                        @if($showCount)
                            <span class="text-xs text-zinc-400 dark:text-zinc-500 bg-zinc-100 dark:bg-zinc-700 px-2 py-0.5 rounded-full">
                                {{ $archive['count'] }}
                            </span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
