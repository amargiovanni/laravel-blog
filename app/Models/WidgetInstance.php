<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WidgetInstance extends Model
{
    /** @use HasFactory<\Database\Factories\WidgetInstanceFactory> */
    use HasFactory;

    protected $fillable = [
        'area',
        'widget_type',
        'title',
        'settings',
        'sort_order',
    ];

    public static function clearWidgetCache(): void
    {
        foreach (array_keys(config('widgets.areas', [])) as $area) {
            Cache::forget("widgets_{$area}");
        }
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, mixed $value): self
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;

        return $this;
    }

    public function getDisplayTitle(): string
    {
        if ($this->title) {
            return $this->title;
        }

        $widgets = config('widgets.types', []);

        return $widgets[$this->widget_type]['name'] ?? ucfirst($this->widget_type);
    }

    public function scopeForArea($query, string $area)
    {
        return $query->where('area', $area)->orderBy('sort_order');
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::clearWidgetCache());
        static::deleted(fn () => self::clearWidgetCache());
    }

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'sort_order' => 'integer',
        ];
    }
}
