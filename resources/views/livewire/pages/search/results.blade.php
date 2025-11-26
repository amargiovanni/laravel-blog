<?php

use App\Models\Setting;
use App\Services\SearchService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('components.layouts.blog')]
#[Title('Search')]
class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $query = '';

    public function updatedQuery(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $searchService = new SearchService();
        $results = null;
        $isValidQuery = false;

        if ($this->query) {
            $isValidQuery = $searchService->isValidQuery($this->query);
            if ($isValidQuery) {
                $results = $searchService->search($this->query);
            }
        }

        return [
            'results' => $results,
            'isValidQuery' => $isValidQuery,
            'searchService' => $searchService,
            'searchTitle' => Setting::get('theme.search_title', __('Search')),
            'searchPlaceholder' => Setting::get('theme.search_placeholder', __('Search posts, categories, tags...')),
        ];
    }
}; ?>

<div class="min-h-[80vh]">
    {{-- Hero Search Section --}}
    <div class="bg-gradient-to-br from-zinc-100 to-zinc-50 dark:from-zinc-800 dark:to-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-16">
            <h1 class="text-4xl font-bold text-center mb-8">{{ $searchTitle }}</h1>

            {{-- Live Search Input --}}
            <div class="relative max-w-2xl mx-auto">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <flux:icon.magnifying-glass class="size-5 text-zinc-400" />
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="query"
                    placeholder="{{ $searchPlaceholder }}"
                    class="w-full pl-12 pr-12 py-4 text-lg rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent shadow-sm transition-all"
                    autofocus
                >
                @if($query)
                    <button
                        type="button"
                        wire:click="$set('query', '')"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors"
                    >
                        <flux:icon.x-mark class="size-5" />
                    </button>
                @endif
            </div>

            @if($query && !$isValidQuery)
                <p class="mt-3 text-sm text-red-500 text-center">{{ __('Please enter at least 2 characters to search.') }}</p>
            @endif

            {{-- Results Count --}}
            @if($query && $isValidQuery && $results)
                <p class="mt-4 text-center text-zinc-600 dark:text-zinc-400">
                    @if($results->total() > 0)
                        {{ __('Found :count results for ":query"', ['count' => $results->total(), 'query' => $query]) }}
                    @else
                        {{ __('No results found for ":query"', ['query' => $query]) }}
                    @endif
                </p>
            @endif
        </div>
    </div>

    {{-- Results Section --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
        {{-- Loading State --}}
        <div wire:loading.delay wire:target="query" class="flex justify-center py-12">
            <div class="flex items-center gap-3 text-zinc-500">
                <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ __('Searching...') }}</span>
            </div>
        </div>

        <div wire:loading.remove wire:target="query">
            @if($query && $isValidQuery)
                @if($results && $results->total() > 0)
                    {{-- Results Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($results as $result)
                            <article class="group bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:shadow-lg transition-shadow">
                                {{-- Featured Image --}}
                                <a href="{{ $result['url'] }}" wire:navigate class="block aspect-video overflow-hidden">
                                    @if($result['image'])
                                        <img
                                            src="{{ $result['image'] }}"
                                            alt="{{ $result['title'] }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        >
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-700 dark:to-zinc-800 flex items-center justify-center">
                                            <flux:icon.document-text class="size-12 text-zinc-400" />
                                        </div>
                                    @endif
                                </a>

                                <div class="p-4">
                                    {{-- Type Badge & Categories --}}
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $result['type'] === 'post' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300' }}">
                                            {{ $result['type'] === 'post' ? __('Post') : __('Page') }}
                                        </span>
                                        @if($result['categories']->isNotEmpty())
                                            @foreach($result['categories']->take(2) as $category)
                                                <span class="text-xs text-accent">{{ $category }}</span>
                                            @endforeach
                                        @endif
                                    </div>

                                    {{-- Title --}}
                                    <h2 class="font-semibold mb-2 line-clamp-2">
                                        <a href="{{ $result['url'] }}" wire:navigate class="hover:text-accent transition-colors">
                                            {!! $searchService->highlightTerms(e($result['title']), $query) !!}
                                        </a>
                                    </h2>

                                    {{-- Excerpt --}}
                                    <p class="text-zinc-600 dark:text-zinc-400 text-sm mb-3 line-clamp-2">
                                        {!! Str::limit(strip_tags($result['excerpt']), 100) !!}
                                    </p>

                                    {{-- Meta --}}
                                    <div class="flex items-center text-xs text-zinc-500 dark:text-zinc-400">
                                        @if($result['author'])
                                            <span>{{ $result['author'] }}</span>
                                        @endif
                                        @if($result['published_at'])
                                            @if($result['author'])<span class="mx-2">&middot;</span>@endif
                                            <time datetime="{{ $result['published_at']->toIso8601String() }}">
                                                {{ $result['published_at']->format('M j, Y') }}
                                            </time>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if($results->hasPages())
                        <div class="mt-12">
                            {{ $results->links() }}
                        </div>
                    @endif
                @else
                    {{-- No Results --}}
                    <div class="text-center py-12">
                        <flux:icon.face-frown class="size-12 text-zinc-300 dark:text-zinc-600 mx-auto mb-4" />
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No results found') }}</h3>
                        <p class="text-zinc-500 dark:text-zinc-400 text-sm">
                            {{ __('Try different keywords or check your spelling.') }}
                        </p>
                    </div>
                @endif
            @elseif(!$query)
                {{-- Initial State --}}
                <div class="text-center py-12">
                    <flux:icon.magnifying-glass class="size-12 text-zinc-300 dark:text-zinc-600 mx-auto mb-4" />
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('Start searching') }}</h3>
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm">
                        {{ __('Enter keywords above to search posts and pages.') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
