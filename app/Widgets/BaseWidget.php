<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\WidgetInstance;
use Illuminate\Contracts\View\View;

abstract class BaseWidget
{
    protected WidgetInstance $instance;

    public function __construct(WidgetInstance $instance)
    {
        $this->instance = $instance;
    }

    abstract public function render(): View;

    abstract public static function getName(): string;

    abstract public static function getDescription(): string;

    /**
     * @return array<string, mixed>
     */
    public static function getDefaultSettings(): array
    {
        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getSettingsFields(): array
    {
        return [];
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->instance->getSetting($key, $default ?? static::getDefaultSettings()[$key] ?? null);
    }

    public function getTitle(): string
    {
        return $this->instance->getDisplayTitle();
    }

    public function getInstance(): WidgetInstance
    {
        return $this->instance;
    }
}
