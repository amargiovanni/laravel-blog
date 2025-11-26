@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'newsletter-form ' . $class]) }}>
    <h3 class="text-lg font-semibold mb-2">{{ __('Subscribe to Newsletter') }}</h3>
    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
        {{ __('Get the latest posts delivered straight to your inbox.') }}
    </p>

    @if (session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('info'))
        <div class="mb-4 p-4 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm">
            {{ session('info') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('newsletter.subscribe') }}" method="POST" class="space-y-3">
        @csrf

        <div>
            <flux:input
                type="email"
                name="email"
                placeholder="{{ __('Enter your email') }}"
                value="{{ old('email') }}"
                required
            />
            @error('email')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <flux:input
                type="text"
                name="name"
                placeholder="{{ __('Your name (optional)') }}"
                value="{{ old('name') }}"
            />
            @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <flux:button type="submit" variant="primary" class="w-full">
            {{ __('Subscribe') }}
        </flux:button>
    </form>

    <p class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">
        {{ __('We respect your privacy. Unsubscribe at any time.') }}
    </p>
</div>
