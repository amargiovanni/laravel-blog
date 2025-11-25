@props(['title' => null, 'post' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeManager" :class="{ 'dark': isDark }">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>{{ $title ?? config('blog.name', config('app.name')) }}</title>
        <meta name="description" content="{{ $metaDescription ?? config('blog.seo.default_meta_description') }}" />

        {{-- JSON-LD Structured Data --}}
        @if($post)
            <x-json-ld type="post" :post="$post" />
        @else
            <x-json-ld type="website" />
        @endif

        {{ $seo ?? '' }}

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <link rel="alternate" type="application/rss+xml" title="{{ config('blog.name') }} RSS Feed" href="{{ url('/feed') }}" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance

        {{-- Theme Settings CSS Variables --}}
        @php
            $themeSettings = [
                '--theme-primary' => \App\Models\Setting::get('theme.primary_color', '#3b82f6'),
                '--theme-secondary' => \App\Models\Setting::get('theme.secondary_color', '#10b981'),
                '--theme-accent' => \App\Models\Setting::get('theme.accent_color', '#8b5cf6'),
                '--theme-text' => \App\Models\Setting::get('theme.text_color', '#1f2937'),
                '--theme-background' => \App\Models\Setting::get('theme.background_color', '#ffffff'),
            ];
        @endphp
        <style>
            :root {
                @foreach($themeSettings as $var => $value)
                {{ $var }}: {{ $value }};
                @endforeach
            }
        </style>
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 antialiased">
        {{-- Header --}}
        <header class="sticky top-0 z-50 border-b border-zinc-200 dark:border-zinc-700 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    {{-- Logo --}}
                    <a href="{{ url('/') }}" class="flex items-center space-x-2" wire:navigate>
                        <x-app-logo class="h-8 w-auto" />
                        <span class="text-xl font-semibold">{{ config('blog.name', config('app.name')) }}</span>
                    </a>

                    {{-- Navigation --}}
                    <nav class="hidden md:flex items-center space-x-8">
                        <a href="{{ url('/') }}" class="text-sm font-medium hover:text-accent transition-colors" wire:navigate>
                            {{ __('Home') }}
                        </a>
                        <a href="{{ url('/posts') }}" class="text-sm font-medium hover:text-accent transition-colors" wire:navigate>
                            {{ __('Blog') }}
                        </a>
                    </nav>

                    {{-- Right side --}}
                    <div class="flex items-center space-x-4">
                        {{-- Search --}}
                        <a href="{{ url('/search') }}" class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200" wire:navigate>
                            <flux:icon.magnifying-glass class="size-5" />
                        </a>

                        {{-- Theme Toggle --}}
                        <button
                            @click="setTheme(isDark ? 'light' : 'dark')"
                            class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                        >
                            <flux:icon.sun x-show="isDark" class="size-5" />
                            <flux:icon.moon x-show="!isDark" x-cloak class="size-5" />
                        </button>

                        {{-- Auth --}}
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium hover:text-accent transition-colors" wire:navigate>
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium hover:text-accent transition-colors" wire:navigate>
                                {{ __('Sign in') }}
                            </a>
                        @endauth

                        {{-- Mobile menu button --}}
                        <button
                            type="button"
                            class="md:hidden text-zinc-500"
                            x-data="{ open: false }"
                            @click="$dispatch('toggle-mobile-menu')"
                        >
                            <flux:icon.bars-3 class="size-6" />
                        </button>
                    </div>
                </div>
            </div>
        </header>

        {{-- Mobile Navigation --}}
        <div
            x-data="{ open: false }"
            @toggle-mobile-menu.window="open = !open"
            x-show="open"
            x-cloak
            class="md:hidden fixed inset-0 z-40"
        >
            <div class="fixed inset-0 bg-black/25" @click="open = false"></div>
            <nav class="fixed top-16 inset-x-0 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex flex-col space-y-4">
                    <a href="{{ url('/') }}" class="text-sm font-medium" wire:navigate @click="open = false">{{ __('Home') }}</a>
                    <a href="{{ url('/posts') }}" class="text-sm font-medium" wire:navigate @click="open = false">{{ __('Blog') }}</a>
                </div>
            </nav>
        </div>

        {{-- Main Content --}}
        <main class="flex-grow">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {{-- About --}}
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider">{{ config('blog.name', config('app.name')) }}</h3>
                        <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ config('blog.description') }}
                        </p>
                    </div>

                    {{-- Links --}}
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider">{{ __('Links') }}</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="{{ url('/') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-accent" wire:navigate>{{ __('Home') }}</a></li>
                            <li><a href="{{ url('/posts') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-accent" wire:navigate>{{ __('Blog') }}</a></li>
                            <li><a href="{{ url('/feed') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-accent">{{ __('RSS Feed') }}</a></li>
                        </ul>
                    </div>

                    {{-- Categories --}}
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider">{{ __('Categories') }}</h3>
                        <ul class="mt-4 space-y-2">
                            {{-- Categories will be populated dynamically --}}
                            {{ $footerCategories ?? '' }}
                        </ul>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center">
                        &copy; {{ date('Y') }} {{ config('blog.name', config('app.name')) }}. {{ __('All rights reserved.') }}
                    </p>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
