@props(['title' => null, 'post' => null, 'showSidebar' => false])

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
            .text-accent { color: var(--theme-accent); }
            .bg-accent { background-color: var(--theme-accent); }
            .hover\:text-accent:hover { color: var(--theme-accent); }
            .hover\:bg-accent:hover { background-color: var(--theme-accent); }
            .border-accent { border-color: var(--theme-accent); }
            .hover\:border-accent:hover { border-color: var(--theme-accent); }
        </style>
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 antialiased">
        {{-- Header --}}
        <header class="sticky top-0 z-50 border-b border-zinc-200 dark:border-zinc-700 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    {{-- Logo --}}
                    @php
                        $headerTitle = \App\Models\Setting::get('theme.site_title') ?: config('blog.name', config('app.name'));
                    @endphp
                    <a href="{{ url('/') }}" class="text-xl font-semibold hover:text-accent transition-colors" wire:navigate>
                        {{ $headerTitle }}
                    </a>

                    {{-- Right side: Navigation + Actions --}}
                    <div class="flex items-center space-x-6">
                        {{-- Main Navigation from Menu Builder --}}
                        <nav class="hidden md:flex items-center space-x-6">
                            @php
                                $headerMenu = \App\Services\MenuService::getItemsForLocation('header');
                            @endphp
                            @if($headerMenu->isNotEmpty())
                                @foreach($headerMenu as $item)
                                    <div class="relative group">
                                        <a
                                            href="{{ $item->getUrl() }}"
                                            target="{{ $item->target }}"
                                            @if($item->title_attribute) title="{{ $item->title_attribute }}" @endif
                                            class="text-sm font-medium hover:text-accent transition-colors {{ $item->css_class ?? '' }}"
                                            @if($item->target === '_self') wire:navigate @endif
                                        >
                                            {{ $item->getDisplayLabel() }}
                                            @if($item->children->isNotEmpty())
                                                <svg class="inline-block w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        </a>
                                        @if($item->children->isNotEmpty())
                                            <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-800 rounded-md shadow-lg border border-zinc-200 dark:border-zinc-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                                <div class="py-1">
                                                    @foreach($item->children as $child)
                                                        <a
                                                            href="{{ $child->getUrl() }}"
                                                            target="{{ $child->target }}"
                                                            class="block px-4 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 {{ $child->css_class ?? '' }}"
                                                            @if($child->target === '_self') wire:navigate @endif
                                                        >
                                                            {{ $child->getDisplayLabel() }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                {{-- Fallback navigation if no menu configured --}}
                                <a href="{{ url('/') }}" class="text-sm font-medium hover:text-accent transition-colors" wire:navigate>
                                    {{ __('Home') }}
                                </a>
                                <a href="{{ route('posts.index') }}" class="text-sm font-medium hover:text-accent transition-colors" wire:navigate>
                                    {{ __('Blog') }}
                                </a>
                                <a href="{{ route('categories.index') }}" class="text-sm font-medium hover:text-accent transition-colors" wire:navigate>
                                    {{ __('Categories') }}
                                </a>
                            @endif
                        </nav>

                        {{-- Search --}}
                        <a href="{{ route('search') }}" class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200" wire:navigate>
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

                        {{-- Mobile menu button --}}
                        <button
                            type="button"
                            class="md:hidden text-zinc-500"
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
            <nav class="fixed top-16 inset-x-0 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 p-4 max-h-[calc(100vh-4rem)] overflow-y-auto">
                <div class="flex flex-col space-y-4">
                    @php
                        $mobileMenu = \App\Services\MenuService::getItemsForLocation('mobile');
                        if ($mobileMenu->isEmpty()) {
                            $mobileMenu = $headerMenu ?? collect();
                        }
                    @endphp
                    @if($mobileMenu->isNotEmpty())
                        @foreach($mobileMenu as $item)
                            <div>
                                <a href="{{ $item->getUrl() }}" class="text-sm font-medium block py-2" @click="open = false" wire:navigate>
                                    {{ $item->getDisplayLabel() }}
                                </a>
                                @if($item->children->isNotEmpty())
                                    <div class="pl-4 space-y-2 mt-2">
                                        @foreach($item->children as $child)
                                            <a href="{{ $child->getUrl() }}" class="text-sm text-zinc-600 dark:text-zinc-400 block py-1" @click="open = false" wire:navigate>
                                                {{ $child->getDisplayLabel() }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <a href="{{ url('/') }}" class="text-sm font-medium" wire:navigate @click="open = false">{{ __('Home') }}</a>
                        <a href="{{ route('posts.index') }}" class="text-sm font-medium" wire:navigate @click="open = false">{{ __('Blog') }}</a>
                        <a href="{{ route('categories.index') }}" class="text-sm font-medium" wire:navigate @click="open = false">{{ __('Categories') }}</a>
                        <a href="{{ route('tags.index') }}" class="text-sm font-medium" wire:navigate @click="open = false">{{ __('Tags') }}</a>
                    @endif
                </div>
            </nav>
        </div>

        {{-- Main Content --}}
        @php
            $sidebarWidgets = \App\Models\WidgetInstance::forArea('primary_sidebar')->get();
            // Show sidebar on blog listing pages if widgets exist
            $sidebarPaths = ['/posts', '/categories/', '/tags/', '/archives'];
            $currentPath = request()->path();
            $isListingPage = collect($sidebarPaths)->contains(fn($path) => $currentPath === ltrim($path, '/') || str_starts_with('/' . $currentPath, $path));
            $showSidebarAuto = $sidebarWidgets->isNotEmpty() && ($showSidebar || $isListingPage);
        @endphp

        <main class="flex-grow">
            @if($showSidebarAuto)
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        {{-- Main Content Area --}}
                        <div class="lg:col-span-8">
                            {{ $slot }}
                        </div>

                        {{-- Primary Sidebar --}}
                        <aside class="lg:col-span-4">
                            <div class="sticky top-24 space-y-6">
                                <x-widget-area area="primary_sidebar" />
                            </div>
                        </aside>
                    </div>
                </div>
            @else
                {{ $slot }}
            @endif
        </main>

        {{-- Footer --}}
        <footer class="border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
                @php
                    $footer1Widgets = \App\Models\WidgetInstance::forArea('footer_1')->get();
                    $footer2Widgets = \App\Models\WidgetInstance::forArea('footer_2')->get();
                    $footer3Widgets = \App\Models\WidgetInstance::forArea('footer_3')->get();
                    $hasAnyWidgets = $footer1Widgets->isNotEmpty() || $footer2Widgets->isNotEmpty() || $footer3Widgets->isNotEmpty();
                @endphp

                @if($hasAnyWidgets)
                    {{-- Widget-based Footer --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {{-- Footer Column 1 --}}
                        <div class="footer-column">
                            <x-widget-area area="footer_1" />
                        </div>

                        {{-- Footer Column 2 --}}
                        <div class="footer-column">
                            <x-widget-area area="footer_2" />
                        </div>

                        {{-- Footer Column 3 --}}
                        <div class="footer-column">
                            <x-widget-area area="footer_3" />
                        </div>
                    </div>
                @else
                    {{-- Default Footer (fallback when no widgets configured) --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        {{-- About --}}
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-semibold uppercase tracking-wider">{{ config('blog.name', config('app.name')) }}</h3>
                            <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ config('blog.description') }}
                            </p>
                        </div>

                        {{-- Footer Navigation --}}
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wider">{{ __('Navigation') }}</h3>
                            @php
                                $footerMenu = \App\Services\MenuService::getItemsForLocation('footer');
                            @endphp
                            <ul class="mt-4 space-y-2">
                                @if($footerMenu->isNotEmpty())
                                    @foreach($footerMenu as $item)
                                        <li>
                                            <a href="{{ $item->getUrl() }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-accent" wire:navigate>
                                                {{ $item->getDisplayLabel() }}
                                            </a>
                                        </li>
                                    @endforeach
                                @else
                                    <li><a href="{{ url('/') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-accent" wire:navigate>{{ __('Home') }}</a></li>
                                    <li><a href="{{ route('posts.index') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-accent" wire:navigate>{{ __('Blog') }}</a></li>
                                    <li><a href="{{ route('archives') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-accent" wire:navigate>{{ __('Archives') }}</a></li>
                                    <li><a href="{{ url('/feed') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-accent">{{ __('RSS Feed') }}</a></li>
                                @endif
                            </ul>
                        </div>

                        {{-- Categories --}}
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wider">{{ __('Categories') }}</h3>
                            @php
                                $footerCategories = \App\Models\Category::withCount(['posts' => fn ($q) => $q->published()])
                                    ->orderBy('name')
                                    ->get()
                                    ->filter(fn ($cat) => $cat->posts_count > 0)
                                    ->take(6);
                            @endphp
                            <ul class="mt-4 space-y-2">
                                @foreach($footerCategories as $category)
                                    <li>
                                        <a href="{{ route('categories.show', $category->slug) }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-accent" wire:navigate>
                                            {{ $category->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="mt-8 pt-8 border-t border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center">
                        @php
                            $footerText = \App\Models\Setting::get('theme.footer_text', '&copy; {year} ' . config('blog.name', config('app.name')) . '. ' . __('All rights reserved.'));
                            $footerText = str_replace('{year}', date('Y'), $footerText);
                        @endphp
                        {!! $footerText !!}
                    </p>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
