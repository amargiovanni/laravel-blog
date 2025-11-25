<div class="widget widget-search">
    @if($title)
        <h3 class="widget-title">{{ $title }}</h3>
    @endif
    <form action="{{ route('search') }}" method="GET" class="search-form">
        <div class="search-input-wrapper">
            <input
                type="search"
                name="q"
                placeholder="{{ $placeholder ?? 'Search...' }}"
                value="{{ request('q') }}"
                class="search-input"
            >
            <button type="submit" class="search-button" aria-label="Search">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </div>
    </form>
</div>
