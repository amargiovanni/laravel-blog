<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\WidgetInstance;
use App\Services\WidgetRegistry;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class WidgetArea extends Component
{
    public Collection $widgets;

    public function __construct(
        public string $area,
        public string $class = '',
    ) {
        $this->widgets = $this->getWidgets();
    }

    public function render(): View|Closure|string
    {
        return view('components.widget-area');
    }

    public function renderWidget(WidgetInstance $instance): string
    {
        $registry = app(WidgetRegistry::class);

        return $registry->render($instance);
    }

    public function hasWidgets(): bool
    {
        return $this->widgets->isNotEmpty();
    }

    protected function getWidgets(): Collection
    {
        $cacheEnabled = config('widgets.cache.enabled', true);
        $cacheTtl = config('widgets.cache.ttl', 3600);

        if ($cacheEnabled) {
            return Cache::remember(
                "widgets_{$this->area}",
                $cacheTtl,
                fn () => $this->loadWidgets()
            );
        }

        return $this->loadWidgets();
    }

    protected function loadWidgets(): Collection
    {
        return WidgetInstance::forArea($this->area)->get();
    }
}
