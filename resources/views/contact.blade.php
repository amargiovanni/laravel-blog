<x-layouts.blog title="{{ __('Contact Us') }}">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-12 md:py-24">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold tracking-tight mb-4">
                {{ __('Contact Us') }}
            </h1>
            <p class="text-lg text-zinc-600 dark:text-zinc-400">
                {{ __('Have a question or want to get in touch? Fill out the form below and we\'ll get back to you as soon as possible.') }}
            </p>
        </div>

        @if (session('success'))
            <div class="mb-8 rounded-lg bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800">
                <div class="flex items-center gap-3">
                    <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400 shrink-0" />
                    <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 md:p-8">
            <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Honeypot field - hidden from users, bots will fill it --}}
                <div class="hidden" aria-hidden="true">
                    <label for="website">{{ __('Website') }}</label>
                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            {{ __('Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-accent focus:border-transparent transition-colors"
                            placeholder="{{ __('Your name') }}"
                        >
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            {{ __('Email') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-accent focus:border-transparent transition-colors"
                            placeholder="{{ __('your@email.com') }}"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        {{ __('Subject') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="subject"
                        id="subject"
                        value="{{ old('subject') }}"
                        required
                        class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-accent focus:border-transparent transition-colors"
                        placeholder="{{ __('What is this about?') }}"
                    >
                    @error('subject')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        {{ __('Message') }} <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        name="message"
                        id="message"
                        rows="6"
                        required
                        maxlength="5000"
                        class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-accent focus:border-transparent transition-colors resize-none"
                        placeholder="{{ __('Your message...') }}"
                    >{{ old('message') }}</textarea>
                    <div class="mt-2 flex justify-between text-sm text-zinc-500 dark:text-zinc-400">
                        @error('message')
                            <p class="text-red-600 dark:text-red-400">{{ $message }}</p>
                        @else
                            <span></span>
                        @enderror
                        <span id="char-count">0 / 5000</span>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-accent hover:bg-accent/90 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent"
                    >
                        <flux:icon.paper-airplane class="size-5" />
                        {{ __('Send Message') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageField = document.getElementById('message');
            const charCount = document.getElementById('char-count');

            if (messageField && charCount) {
                function updateCount() {
                    charCount.textContent = messageField.value.length + ' / 5000';
                }
                messageField.addEventListener('input', updateCount);
                updateCount();
            }
        });
    </script>
</x-layouts.blog>
