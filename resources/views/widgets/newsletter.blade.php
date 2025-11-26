<div class="widget widget-newsletter">
    @if($title)
        <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-900 dark:text-white mb-4">{{ $title }}</h3>
    @endif

    @if($description)
        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">{{ $description }}</p>
    @endif

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 p-3">
            <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('info'))
        <div class="mb-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 p-3">
            <p class="text-sm text-blue-700 dark:text-blue-400">{{ session('info') }}</p>
        </div>
    @endif

    <form action="{{ route('newsletter.subscribe') }}" method="POST" class="space-y-3">
        @csrf

        @if($showNameField)
            <div>
                <input
                    type="text"
                    name="name"
                    placeholder="{{ __('Your name (optional)') }}"
                    value="{{ old('name') }}"
                    class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-accent focus:border-transparent text-sm placeholder-zinc-400 dark:placeholder-zinc-500"
                >
                @error('name')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div>
            <input
                type="email"
                name="email"
                placeholder="{{ __('Your email address') }}"
                value="{{ old('email') }}"
                required
                class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-accent focus:border-transparent text-sm placeholder-zinc-400 dark:placeholder-zinc-500"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="w-full px-4 py-2.5 bg-accent hover:bg-accent/90 text-white font-medium rounded-lg text-sm transition-colors"
        >
            {{ $buttonText }}
        </button>
    </form>
</div>
