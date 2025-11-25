<?php

use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('components.layouts.blog')]
class extends Component {
    use WithPagination;

    public int $year;
    public ?int $month = null;

    public function mount(int $year, ?string $month = null): void
    {
        $this->year = $year;
        $this->month = $month ? (int) $month : null;
    }

    public function with(): array
    {
        $driver = DB::getDriverName();
        $query = Post::published()
            ->with(['author', 'categories', 'featuredImage']);

        if ($driver === 'sqlite') {
            $query->whereRaw("strftime('%Y', published_at) = ?", [(string) $this->year]);
            if ($this->month) {
                $query->whereRaw("strftime('%m', published_at) = ?", [str_pad((string) $this->month, 2, '0', STR_PAD_LEFT)]);
            }
        } else {
            $query->whereYear('published_at', $this->year);
            if ($this->month) {
                $query->whereMonth('published_at', $this->month);
            }
        }

        $title = $this->month
            ? Carbon::create($this->year, $this->month)->format('F Y')
            : (string) $this->year;

        return [
            'posts' => $query->latest('published_at')->paginate(config('blog.posts.per_page', 10)),
            'title' => $title,
        ];
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('archives') }}" wire:navigate class="text-sm text-accent hover:underline mb-2 inline-block">
            &larr; {{ __('All Archives') }}
        </a>
        <h1 class="text-3xl font-bold">{{ $title }}</h1>
        <p class="mt-2 text-zinc-600 dark:text-zinc-400">
            {{ __('Posts from :date', ['date' => $title]) }}
        </p>
    </div>

    {{-- Posts List --}}
    @if($posts->isEmpty())
        <div class="text-center py-12">
            <p class="text-zinc-500 dark:text-zinc-400">{{ __('No posts found for this period.') }}</p>
        </div>
    @else
        <div class="space-y-8">
            @foreach($posts as $post)
                <article class="flex flex-col md:flex-row gap-6 p-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    @if($post->featuredImage)
                        <a href="{{ route('posts.show', $post->slug) }}" wire:navigate class="md:w-64 md:shrink-0">
                            <img
                                src="{{ $post->featuredImage->url }}"
                                alt="{{ $post->title }}"
                                class="w-full h-48 md:h-full object-cover rounded-md"
                            />
                        </a>
                    @endif

                    <div class="flex-1">
                        @if($post->categories->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mb-2">
                                @foreach($post->categories as $category)
                                    <a href="{{ route('categories.show', $category->slug) }}" wire:navigate class="text-xs font-medium text-accent hover:underline">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <h2 class="text-xl font-semibold mb-2">
                            <a href="{{ route('posts.show', $post->slug) }}" wire:navigate class="hover:text-accent transition-colors">
                                {{ $post->title }}
                            </a>
                        </h2>

                        <p class="text-zinc-600 dark:text-zinc-400 text-sm mb-4 line-clamp-2">
                            {{ $post->excerpt }}
                        </p>

                        <div class="flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                            <span>{{ $post->author->name }}</span>
                            <span>&middot;</span>
                            <time datetime="{{ $post->published_at->toIso8601String() }}">
                                {{ $post->published_at->format('M j, Y') }}
                            </time>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    @endif
</div>
