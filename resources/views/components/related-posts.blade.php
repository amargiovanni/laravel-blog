<section class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-700">
    <h2 class="text-2xl font-bold mb-6">{{ __('Related Posts') }}</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($relatedPosts as $relatedPost)
            <article class="group">
                <a href="{{ route('posts.show', $relatedPost->slug) }}" wire:navigate class="block">
                    {{-- Featured Image --}}
                    <div class="aspect-video rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 mb-3">
                        @if($relatedPost->featuredImage)
                            <img
                                src="{{ $relatedPost->featuredImage->url }}"
                                alt="{{ $relatedPost->title }}"
                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                loading="lazy"
                            >
                        @else
                            <div class="w-full h-full flex items-center justify-center text-zinc-400 dark:text-zinc-600">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Title --}}
                    <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-accent transition-colors line-clamp-2">
                        {{ $relatedPost->title }}
                    </h3>

                    {{-- Date --}}
                    <time
                        datetime="{{ $relatedPost->published_at?->toISOString() }}"
                        class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                    >
                        {{ $relatedPost->published_at?->format('M j, Y') }}
                    </time>
                </a>
            </article>
        @endforeach
    </div>
</section>
