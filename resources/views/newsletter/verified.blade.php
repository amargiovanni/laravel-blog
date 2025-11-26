<x-layouts.blog title="{{ __('Subscription Confirmed') }}">
    <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/30 mb-6">
                <flux:icon.check class="size-8 text-green-600 dark:text-green-400" />
            </div>

            <h1 class="text-3xl font-bold tracking-tight mb-4">
                {{ __('Subscription Confirmed!') }}
            </h1>

            <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8">
                {{ __('Thank you for confirming your subscription. You will now receive our latest updates and newsletters.') }}
            </p>

            <a href="{{ url('/') }}" wire:navigate class="inline-flex items-center gap-2 text-accent font-medium hover:underline">
                <flux:icon.arrow-left class="size-4" />
                {{ __('Back to homepage') }}
            </a>
        </div>
    </div>
</x-layouts.blog>
