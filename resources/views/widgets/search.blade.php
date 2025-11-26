<div class="widget widget-search bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
    @if($title)
        <h3 class="widget-title font-semibold mb-3">{{ $title }}</h3>
    @endif
    <form action="{{ route('search') }}" method="GET">
        <div class="relative">
            <input
                type="search"
                name="q"
                placeholder="{{ $placeholder ?? __('Search...') }}"
                value="{{ request('q') }}"
                minlength="2"
                maxlength="100"
                class="w-full px-4 py-2 pr-10 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-accent focus:border-transparent text-sm"
            >
            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-accent" aria-label="{{ __('Search') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </div>
    </form>
</div>
