<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    private const CACHE_TTL = 3600; // 1 hour

    public function getMenuItems(string $location): Collection
    {
        return Cache::remember(
            "menu_items_{$location}",
            self::CACHE_TTL,
            fn () => $this->loadMenuItems($location)
        );
    }

    public function getMenu(string $location): ?Menu
    {
        return Cache::remember(
            "menu_{$location}",
            self::CACHE_TTL,
            fn () => Menu::findByLocation($location)
        );
    }

    public function clearCache(?string $location = null): void
    {
        if ($location) {
            Cache::forget("menu_{$location}");
            Cache::forget("menu_items_{$location}");

            return;
        }

        foreach (['header', 'footer', 'mobile'] as $loc) {
            Cache::forget("menu_{$loc}");
            Cache::forget("menu_items_{$loc}");
        }
    }

    public function buildTree(Collection $items, ?int $parentId = null): Collection
    {
        return $items
            ->filter(fn ($item) => $item->parent_id === $parentId)
            ->map(function ($item) use ($items) {
                $item->setRelation('children', $this->buildTree($items, $item->id));

                return $item;
            })
            ->values();
    }

    protected function loadMenuItems(string $location): Collection
    {
        $menu = Menu::findByLocation($location);

        if (! $menu) {
            return collect();
        }

        return $menu->items()
            ->with(['children', 'linkable'])
            ->get();
    }
}
