<div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
    @if ($submitted)
        <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800">
            <div class="flex items-center gap-3">
                <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400 shrink-0" />
                <div>
                    <p class="text-sm font-medium text-green-700 dark:text-green-300">
                        {{ __('Thank you for your comment!') }}
                    </p>
                    @if (config('comments.require_moderation', true))
                        <p class="text-sm text-green-600 dark:text-green-400 mt-1">
                            {{ __('Your comment is awaiting moderation and will appear shortly.') }}
                        </p>
                    @endif
                </div>
            </div>
            <button
                type="button"
                wire:click="$set('submitted', false)"
                class="mt-4 text-sm text-green-700 dark:text-green-300 hover:underline"
            >
                {{ __('Leave another comment') }}
            </button>
        </div>
    @else
        <form wire:submit="submit" class="space-y-6">
            {{-- Honeypot field - hidden from users, bots will fill it --}}
            <div class="hidden" aria-hidden="true">
                <label for="website">{{ __('Website') }}</label>
                <input type="text" wire:model="website" id="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="authorName" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        {{ __('Name') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        wire:model="authorName"
                        id="authorName"
                        required
                        @if(auth()->check()) readonly @endif
                        class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-accent focus:border-transparent transition-colors @if(auth()->check()) bg-zinc-100 dark:bg-zinc-800 @endif"
                        placeholder="{{ __('Your name') }}"
                    >
                    @error('authorName')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="authorEmail" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        {{ __('Email') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        wire:model="authorEmail"
                        id="authorEmail"
                        required
                        @if(auth()->check()) readonly @endif
                        class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-accent focus:border-transparent transition-colors @if(auth()->check()) bg-zinc-100 dark:bg-zinc-800 @endif"
                        placeholder="{{ __('your@email.com') }}"
                    >
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('Your email will not be published.') }}
                    </p>
                    @error('authorEmail')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="content" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                    {{ __('Comment') }} <span class="text-red-500">*</span>
                </label>
                <textarea
                    wire:model="content"
                    id="content"
                    rows="4"
                    required
                    maxlength="{{ config('comments.max_length', 2000) }}"
                    class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-accent focus:border-transparent transition-colors resize-none"
                    placeholder="{{ __('Share your thoughts...') }}"
                ></textarea>
                <div class="mt-2 flex justify-between text-sm text-zinc-500 dark:text-zinc-400">
                    @error('content')
                        <p class="text-red-600 dark:text-red-400">{{ $message }}</p>
                    @else
                        <span></span>
                    @enderror
                    <span>{{ strlen($content ?? '') }} / {{ config('comments.max_length', 2000) }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    wire:model="notifyReplies"
                    id="notifyReplies"
                    class="rounded border-zinc-300 dark:border-zinc-600 text-accent focus:ring-accent"
                >
                <label for="notifyReplies" class="text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Notify me of replies via email') }}
                </label>
            </div>

            <div class="flex items-center justify-end">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-accent hover:bg-accent/90 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="submit">
                        <flux:icon.chat-bubble-left class="size-5" />
                    </span>
                    <span wire:loading wire:target="submit">
                        <flux:icon.arrow-path class="size-5 animate-spin" />
                    </span>
                    <span wire:loading.remove wire:target="submit">{{ __('Post Comment') }}</span>
                    <span wire:loading wire:target="submit">{{ __('Posting...') }}</span>
                </button>
            </div>
        </form>
    @endif
</div>
