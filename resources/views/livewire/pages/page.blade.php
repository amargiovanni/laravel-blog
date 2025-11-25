<?php

use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.blog')]
class extends Component {
    public Page $page;

    public function mount(string $slug): void
    {
        $this->page = Page::published()
            ->with(['author', 'featuredImage'])
            ->where('slug', $slug)
            ->firstOrFail();
    }
}; ?>

<article class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-12">
    {{-- Header --}}
    <header class="mb-8">
        {{-- Breadcrumbs --}}
        @if($page->parent_id)
            <nav class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-4">
                <a href="{{ url('/') }}" wire:navigate class="hover:text-accent">{{ __('Home') }}</a>
                @foreach($page->getBreadcrumbs() as $crumb)
                    <span>/</span>
                    @if($loop->last)
                        <span class="text-zinc-700 dark:text-zinc-300">{{ $crumb['title'] }}</span>
                    @else
                        <a href="{{ $crumb['url'] }}" wire:navigate class="hover:text-accent">{{ $crumb['title'] }}</a>
                    @endif
                @endforeach
            </nav>
        @endif

        {{-- Title --}}
        <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
            {{ $page->title }}
        </h1>

        {{-- Meta --}}
        @if($page->updated_at && $page->updated_at->diffInDays($page->created_at) > 0)
            <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Last updated') }}: {{ $page->updated_at->format('F j, Y') }}
            </p>
        @endif
    </header>

    {{-- Featured Image --}}
    @if($page->featuredImage)
        <figure class="mb-8 -mx-4 sm:-mx-6 lg:-mx-8">
            <img
                src="{{ $page->featuredImage->url }}"
                alt="{{ $page->title }}"
                class="w-full rounded-lg"
            />
        </figure>
    @endif

    {{-- Content --}}
    <div class="prose prose-lg dark:prose-invert max-w-none">
        {!! $page->content !!}
    </div>

    {{-- Child Pages --}}
    @if($page->children->isNotEmpty())
        <nav class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-700">
            <h2 class="text-xl font-semibold mb-4">{{ __('Related pages') }}</h2>
            <ul class="space-y-2">
                @foreach($page->children->where('status', 'published') as $child)
                    <li>
                        <a href="{{ $child->getUrl() }}" wire:navigate class="text-accent hover:underline">
                            {{ $child->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif
</article>
