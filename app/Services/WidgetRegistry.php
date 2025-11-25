<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WidgetInstance;
use App\Widgets\BaseWidget;
use Illuminate\Support\Collection;
use Throwable;

class WidgetRegistry
{
    /**
     * @return Collection<string, array{name: string, description: string, class: string}>
     */
    public function getAvailableWidgets(): Collection
    {
        return collect(config('widgets.types', []));
    }

    /**
     * @return Collection<string, array{name: string, description: string}>
     */
    public function getWidgetAreas(): Collection
    {
        return collect(config('widgets.areas', []));
    }

    public function getWidgetClass(string $type): ?string
    {
        return config("widgets.types.{$type}.class");
    }

    public function createWidget(WidgetInstance $instance): ?BaseWidget
    {
        $class = $this->getWidgetClass($instance->widget_type);

        if (! $class || ! class_exists($class)) {
            return null;
        }

        return new $class($instance);
    }

    public function render(WidgetInstance $instance): string
    {
        $widget = $this->createWidget($instance);

        if (! $widget) {
            return '';
        }

        try {
            return $widget->render()->render();
        } catch (Throwable $e) {
            report($e);

            return '';
        }
    }

    public function getWidgetName(string $type): string
    {
        return config("widgets.types.{$type}.name", ucfirst($type));
    }

    public function getWidgetDescription(string $type): string
    {
        return config("widgets.types.{$type}.description", '');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSettingsFields(string $type): array
    {
        $class = $this->getWidgetClass($type);

        if (! $class || ! class_exists($class)) {
            return [];
        }

        return $class::getSettingsFields();
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultSettings(string $type): array
    {
        $class = $this->getWidgetClass($type);

        if (! $class || ! class_exists($class)) {
            return [];
        }

        return $class::getDefaultSettings();
    }
}
