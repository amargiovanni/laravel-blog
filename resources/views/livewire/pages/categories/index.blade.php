<?php

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.blog')]
#[Title('Categories')]
class extends Component {
    public function with(): array
    {
        return [
            'categories' => Category::withCount(['posts' => fn ($query) => $query->published()])
                ->orderBy('name')
                ->get()
                ->filter(fn ($cat) => $cat->posts_count > 0),
        ];
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold">{{ __('Categories') }}</h1>
        <p class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('Browse posts by category') }}</p>
    </div>

    {{-- Categories Grid --}}
    @if($categories->isEmpty())
        <div class="text-center py-12">
            <p class="text-zinc-500 dark:text-zinc-400">{{ __('No categories found.') }}</p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($categories as $category)
                <a
                    href="{{ route('categories.show', $category->slug) }}"
                    wire:navigate
                    class="group p-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-accent dark:hover:border-accent transition-colors"
                >
                    <h2 class="text-xl font-semibold group-hover:text-accent transition-colors">
                        {{ $category->name }}
                    </h2>
                    @if($category->description)
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">
                            {{ $category->description }}
                        </p>
                    @endif
                    <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}
                    </p>
                </a>
            @endforeach
        </div>
    @endif
</div>
