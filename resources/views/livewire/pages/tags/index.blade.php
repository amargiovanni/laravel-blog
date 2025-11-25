<?php

use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.blog')]
#[Title('Tags')]
class extends Component {
    public function with(): array
    {
        $tags = Tag::withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get()
            ->filter(fn ($tag) => $tag->posts_count > 0)
            ->values();

        // Calculate tag sizes for cloud effect
        if ($tags->isNotEmpty()) {
            $maxCount = $tags->max('posts_count');
            $minCount = $tags->min('posts_count');
            $range = max($maxCount - $minCount, 1);

            $tags->each(function ($tag) use ($minCount, $range) {
                // Size ranges from 0.875 to 1.5
                $tag->size = 0.875 + (($tag->posts_count - $minCount) / $range) * 0.625;
            });
        }

        return [
            'tags' => $tags,
        ];
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold">{{ __('Tags') }}</h1>
        <p class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('Browse posts by tag') }}</p>
    </div>

    {{-- Tag Cloud --}}
    @if($tags->isEmpty())
        <div class="text-center py-12">
            <p class="text-zinc-500 dark:text-zinc-400">{{ __('No tags found.') }}</p>
        </div>
    @else
        <div class="flex flex-wrap gap-3">
            @foreach($tags as $tag)
                <a
                    href="{{ route('tags.show', $tag->slug) }}"
                    wire:navigate
                    class="inline-flex items-center px-4 py-2 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-accent hover:text-white transition-colors"
                    style="font-size: {{ $tag->size }}rem"
                    title="{{ $tag->posts_count }} {{ Str::plural('post', $tag->posts_count) }}"
                >
                    #{{ $tag->name }}
                    <span class="ml-2 text-xs opacity-60">{{ $tag->posts_count }}</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
