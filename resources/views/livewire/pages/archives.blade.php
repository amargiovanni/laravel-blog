<?php

use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.blog')]
#[Title('Archives')]
class extends Component {
    public function with(): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $archives = Post::published()
                ->selectRaw("strftime('%Y', published_at) as year, strftime('%m', published_at) as month, COUNT(*) as count")
                ->groupByRaw("strftime('%Y', published_at), strftime('%m', published_at)")
                ->orderByRaw('year DESC, month DESC')
                ->get()
                ->map(fn ($item) => (object) ['year' => (int) $item->year, 'month' => (int) $item->month, 'count' => $item->count])
                ->groupBy('year');
        } else {
            $archives = Post::published()
                ->selectRaw('YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as count')
                ->groupByRaw('YEAR(published_at), MONTH(published_at)')
                ->orderByRaw('year DESC, month DESC')
                ->get()
                ->groupBy('year');
        }

        return [
            'archives' => $archives,
        ];
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold">{{ __('Archives') }}</h1>
        <p class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('Browse posts by date') }}</p>
    </div>

    {{-- Archives List --}}
    @if($archives->isEmpty())
        <div class="text-center py-12">
            <p class="text-zinc-500 dark:text-zinc-400">{{ __('No archived posts found.') }}</p>
        </div>
    @else
        <div class="space-y-8">
            @foreach($archives as $year => $months)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-900/50 border-b border-zinc-200 dark:border-zinc-700">
                        <h2 class="text-xl font-semibold">{{ $year }}</h2>
                    </div>
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($months as $month)
                            <a
                                href="{{ route('archives.filter', ['year' => $year, 'month' => str_pad($month->month, 2, '0', STR_PAD_LEFT)]) }}"
                                wire:navigate
                                class="flex items-center justify-between px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors"
                            >
                                <span class="font-medium">
                                    {{ Carbon::create($year, $month->month)->format('F') }}
                                </span>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $month->count }} {{ Str::plural('post', $month->count) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
