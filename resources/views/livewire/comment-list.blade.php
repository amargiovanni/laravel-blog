<div class="space-y-6">
    @if ($comments->isEmpty())
        <p class="text-zinc-500 dark:text-zinc-400 text-center py-8">
            {{ __('No comments yet. Be the first to share your thoughts!') }}
        </p>
    @else
        @foreach ($comments as $comment)
            <x-comment
                :comment="$comment"
                :replying-to="$replyingTo"
                :post="$post"
                :depth="0"
            />
        @endforeach
    @endif
</div>
