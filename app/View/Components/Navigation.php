<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Services\MenuService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Navigation extends Component
{
    public Collection $items;

    public function __construct(
        public string $location = 'header',
        public string $class = '',
    ) {
        $this->items = app(MenuService::class)->getMenuItems($location);
    }

    public function render(): View|Closure|string
    {
        return view('components.navigation');
    }

    public function hasItems(): bool
    {
        return $this->items->isNotEmpty();
    }
}
