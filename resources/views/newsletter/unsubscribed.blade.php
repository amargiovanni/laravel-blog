<x-layouts.blog title="{{ __('Unsubscribed') }}">
    <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/30 mb-6">
                <flux:icon.check class="size-8 text-green-600 dark:text-green-400" />
            </div>

            <h1 class="text-3xl font-bold tracking-tight mb-4">
                {{ __('Successfully Unsubscribed') }}
            </h1>

            <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8">
                {{ __('You have been unsubscribed from our newsletter. You will no longer receive emails from us.') }}
            </p>

            <a href="{{ url('/') }}" wire:navigate class="inline-flex items-center gap-2 text-accent font-medium hover:underline">
                <flux:icon.arrow-left class="size-4" />
                {{ __('Back to homepage') }}
            </a>
        </div>
    </div>
</x-layouts.blog>
