<?php

use App\Services\SearchService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('components.layouts.blog')]
#[Title('Search Results')]
class extends Component {
    use WithPagination;

    public string $query = '';

    public function mount(): void
    {
        $this->query = request()->input('q', '');
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
        ];
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
    {{-- Search Form --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-4">{{ __('Search') }}</h1>
        <form action="{{ route('search') }}" method="GET" class="max-w-2xl">
            <div class="flex gap-2">
                <div class="flex-1 relative">
                    <input
                        type="search"
                        name="q"
                        value="{{ $query }}"
                        placeholder="{{ __('Search posts, pages, categories, tags...') }}"
                        minlength="2"
                        maxlength="100"
                        class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-accent focus:border-transparent"
                        autofocus
                    >
                </div>
                <button
                    type="submit"
                    class="px-6 py-3 bg-accent text-white rounded-lg hover:bg-accent/90 transition-colors flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    {{ __('Search') }}
                </button>
            </div>
            @if($query && !$isValidQuery)
                <p class="mt-2 text-sm text-red-500">{{ __('Please enter at least 2 characters to search.') }}</p>
            @endif
        </form>
    </div>

    {{-- Results --}}
    @if($query && $isValidQuery)
        @if($results && $results->total() > 0)
            <div class="mb-4">
                <p class="text-zinc-600 dark:text-zinc-400">
                    {{ __('Found :count results for ":query"', ['count' => $results->total(), 'query' => $query]) }}
                </p>
            </div>

            <div class="space-y-6">
                @foreach($results as $result)
                    <article class="flex flex-col md:flex-row gap-6 p-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        {{-- Featured Image --}}
                        @if($result['image'])
                            <a href="{{ $result['url'] }}" wire:navigate class="md:w-48 md:shrink-0">
                                <img
                                    src="{{ $result['image'] }}"
                                    alt="{{ $result['title'] }}"
                                    class="w-full h-32 md:h-full object-cover rounded-md"
                                >
                            </a>
                        @endif

                        <div class="flex-1">
                            {{-- Type Badge --}}
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $result['type'] === 'post' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' }}">
                                    {{ $result['type'] === 'post' ? __('Post') : __('Page') }}
                                </span>
                                @if($result['categories']->isNotEmpty())
                                    @foreach($result['categories'] as $category)
                                        <span class="text-xs text-accent">{{ $category }}</span>
                                    @endforeach
                                @endif
                            </div>

                            {{-- Title --}}
                            <h2 class="text-xl font-semibold mb-2">
                                <a href="{{ $result['url'] }}" wire:navigate class="hover:text-accent transition-colors">
                                    {!! $searchService->highlightTerms(e($result['title']), $query) !!}
                                </a>
                            </h2>

                            {{-- Excerpt with highlighting --}}
                            <p class="text-zinc-600 dark:text-zinc-400 text-sm mb-4 line-clamp-3">
                                {!! $result['excerpt'] !!}
                            </p>

                            {{-- Meta --}}
                            <div class="flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                                @if($result['author'])
                                    <span>{{ $result['author'] }}</span>
                                    <span>&middot;</span>
                                @endif
                                @if($result['published_at'])
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
            <div class="mt-8">
                {{ $results->withQueryString()->links() }}
            </div>
        @else
            {{-- No Results --}}
            <div class="text-center py-12 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-zinc-100">{{ __('No results found') }}</h3>
                <p class="mt-2 text-zinc-500 dark:text-zinc-400">
                    {{ __('No results found for ":query". Try different keywords or check the spelling.', ['query' => $query]) }}
                </p>
                <div class="mt-6">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">{{ __('Suggestions:') }}</p>
                    <ul class="text-sm text-zinc-600 dark:text-zinc-300 space-y-1">
                        <li>{{ __('Check your spelling') }}</li>
                        <li>{{ __('Try more general keywords') }}</li>
                        <li>{{ __('Try different keywords') }}</li>
                    </ul>
                </div>
            </div>
        @endif
    @elseif(!$query)
        {{-- Initial State --}}
        <div class="text-center py-12 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-zinc-100">{{ __('Search the blog') }}</h3>
            <p class="mt-2 text-zinc-500 dark:text-zinc-400">
                {{ __('Enter keywords to search through posts and pages.') }}
            </p>
        </div>
    @endif
</div>
