<div
    id="comment-{{ $comment->id }}"
    class="@if($depth > 0) ml-6 md:ml-12 border-l-2 border-zinc-200 dark:border-zinc-700 pl-4 md:pl-6 @endif"
>
    <article class="bg-white dark:bg-zinc-800 rounded-lg p-4 md:p-6 shadow-sm border border-zinc-200 dark:border-zinc-700">
        {{-- Comment Header --}}
        <header class="flex items-start gap-4 mb-4">
            {{-- Avatar --}}
            <img
                src="{{ $comment->getGravatarUrl() }}"
                alt="{{ $comment->author_name }}"
                class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-zinc-200 dark:bg-zinc-700 shrink-0"
                loading="lazy"
            >

            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-medium text-zinc-900 dark:text-zinc-100">
                        {{ $comment->author_name }}
                    </span>
                    @if ($comment->user_id)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-accent/10 text-accent">
                            {{ __('Author') }}
                        </span>
                    @endif
                </div>
                <time
                    datetime="{{ $comment->created_at->toIso8601String() }}"
                    class="text-sm text-zinc-500 dark:text-zinc-400"
                    title="{{ $comment->created_at->format('F j, Y g:i A') }}"
                >
                    {{ $comment->created_at->diffForHumans() }}
                </time>
            </div>
        </header>

        {{-- Comment Content --}}
        <div class="prose prose-sm dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300">
            {!! nl2br(e($comment->content)) !!}
        </div>

        {{-- Comment Actions --}}
        @if ($canReply())
            <footer class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-700">
                @if ($replyingTo === $comment->id)
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                {{ __('Replying to :name', ['name' => $comment->author_name]) }}
                            </span>
                            <button
                                type="button"
                                wire:click="cancelReply"
                                class="text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                            >
                                {{ __('Cancel') }}
                            </button>
                        </div>
                        <livewire:comment-form :post="$post" :parent-id="$comment->id" :key="'reply-'.$comment->id" />
                    </div>
                @else
                    <button
                        type="button"
                        wire:click="startReply({{ $comment->id }})"
                        class="inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-accent dark:text-zinc-400 dark:hover:text-accent transition-colors"
                    >
                        <flux:icon.chat-bubble-left class="size-4" />
                        {{ __('Reply') }}
                    </button>
                @endif
            </footer>
        @endif
    </article>

    {{-- Nested Replies --}}
    @if ($comment->approvedReplies->isNotEmpty())
        <div class="mt-4 space-y-4">
            @foreach ($comment->approvedReplies as $reply)
                <x-comment
                    :comment="$reply"
                    :replying-to="$replyingTo"
                    :post="$post"
                    :depth="$depth + 1"
                />
            @endforeach
        </div>
    @endif
</div>
