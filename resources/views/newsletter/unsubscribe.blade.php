<x-layouts.blog title="{{ __('Unsubscribe') }}">
    <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 dark:bg-amber-900/30 mb-6">
                <flux:icon.envelope class="size-8 text-amber-600 dark:text-amber-400" />
            </div>

            <h1 class="text-3xl font-bold tracking-tight mb-4">
                {{ __('Unsubscribe from Newsletter') }}
            </h1>

            <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8">
                {{ __('Are you sure you want to unsubscribe :email from our newsletter?', ['email' => $subscriber->email]) }}
            </p>

            <form action="{{ route('newsletter.unsubscribe.confirm', $token) }}" method="POST" class="space-y-4">
                @csrf

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <flux:button type="submit" variant="danger">
                        {{ __('Yes, Unsubscribe') }}
                    </flux:button>

                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg text-sm font-medium hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                        {{ __('No, Keep Me Subscribed') }}
                    </a>
                </div>
            </form>

            <p class="mt-8 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('We are sorry to see you go. You can always resubscribe later.') }}
            </p>
        </div>
    </div>
</x-layouts.blog>
