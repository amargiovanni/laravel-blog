<x-layouts.blog title="{{ __('Already Unsubscribed') }}">
    <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900/30 mb-6">
                <flux:icon.information-circle class="size-8 text-blue-600 dark:text-blue-400" />
            </div>

            <h1 class="text-3xl font-bold tracking-tight mb-4">
                {{ __('Already Unsubscribed') }}
            </h1>

            <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8">
                {{ __('This email address has already been unsubscribed from our newsletter.') }}
            </p>

            <a href="{{ url('/') }}" wire:navigate class="inline-flex items-center gap-2 text-accent font-medium hover:underline">
                <flux:icon.arrow-left class="size-4" />
                {{ __('Back to homepage') }}
            </a>
        </div>
    </div>
</x-layouts.blog>
