<?php

declare(strict_types=1);

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use App\Services\MenuService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $permissions = [
        'view_any_menu',
        'view_menu',
        'create_menu',
        'update_menu',
        'delete_menu',
        'delete_any_menu',
        'reorder_menu',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo($permissions);
});

describe('Menu Model', function (): void {
    test('menu can be created with name and location', function (): void {
        $menu = Menu::factory()->header()->create(['name' => 'Main Navigation']);

        expect($menu->name)->toBe('Main Navigation');
        expect($menu->location)->toBe('header');
    });

    test('menu can find by location', function (): void {
        $menu = Menu::factory()->footer()->create(['name' => 'Footer Links']);

        $found = Menu::findByLocation('footer');

        expect($found)->not->toBeNull();
        expect($found->id)->toBe($menu->id);
    });

    test('menu returns null for non-existent location', function (): void {
        $found = Menu::findByLocation('header');

        expect($found)->toBeNull();
    });

    test('menu has items relationship', function (): void {
        $menu = Menu::factory()->create();
        MenuItem::factory()->forMenu($menu)->count(3)->create();

        expect($menu->items)->toHaveCount(3);
    });

    test('menu items() only returns root items', function (): void {
        $menu = Menu::factory()->create();
        $parent = MenuItem::factory()->forMenu($menu)->create();
        MenuItem::factory()->forMenu($menu)->asChild($parent)->create();
        MenuItem::factory()->forMenu($menu)->create();

        // items() should only return root level items
        expect($menu->items)->toHaveCount(2);
        // allItems() should return all items
        expect($menu->allItems)->toHaveCount(3);
    });

    test('menu can check if assigned to location', function (): void {
        $assigned = Menu::factory()->header()->create();
        $unassigned = Menu::factory()->create(['location' => 'none']);

        expect($assigned->isAssignedToLocation())->toBeTrue();
        expect($unassigned->isAssignedToLocation())->toBeFalse();
    });
});

describe('MenuItem Model', function (): void {
    test('menu item can be created with label', function (): void {
        $menu = Menu::factory()->create();
        $item = MenuItem::factory()->forMenu($menu)->create(['label' => 'Home']);

        expect($item->label)->toBe('Home');
        expect($item->menu_id)->toBe($menu->id);
    });

    test('menu item can have custom URL', function (): void {
        $item = MenuItem::factory()->customLink('https://example.com', 'External')->create();

        expect($item->url)->toBe('https://example.com');
        expect($item->getUrl())->toBe('https://example.com');
    });

    test('menu item can open in new tab', function (): void {
        $item = MenuItem::factory()->openInNewTab()->create();

        expect($item->target)->toBe('_blank');
    });

    test('menu item can have parent-child relationship', function (): void {
        $menu = Menu::factory()->create();
        $parent = MenuItem::factory()->forMenu($menu)->create(['label' => 'Services']);
        $child = MenuItem::factory()->asChild($parent)->create(['label' => 'Consulting']);

        expect($child->parent->id)->toBe($parent->id);
        expect($parent->children)->toHaveCount(1);
        expect($parent->children->first()->label)->toBe('Consulting');
    });

    test('menu item depth is calculated correctly', function (): void {
        $menu = Menu::factory()->create();
        $root = MenuItem::factory()->forMenu($menu)->create();
        $child = MenuItem::factory()->asChild($root)->create();
        $grandchild = MenuItem::factory()->asChild($child)->create();

        expect($root->getDepth())->toBe(0);
        expect($child->getDepth())->toBe(1);
        expect($grandchild->getDepth())->toBe(2);
    });

    test('menu item can have children only if depth < 2', function (): void {
        $menu = Menu::factory()->create();
        $root = MenuItem::factory()->forMenu($menu)->create();
        $child = MenuItem::factory()->asChild($root)->create();
        $grandchild = MenuItem::factory()->asChild($child)->create();

        expect($root->canHaveChildren())->toBeTrue();
        expect($child->canHaveChildren())->toBeTrue();
        expect($grandchild->canHaveChildren())->toBeFalse(); // Max depth reached
    });

    test('menu item hasChildren returns correct value', function (): void {
        $menu = Menu::factory()->create();
        $parent = MenuItem::factory()->forMenu($menu)->create();
        $child = MenuItem::factory()->asChild($parent)->create();

        expect($parent->hasChildren())->toBeTrue();
        expect($child->hasChildren())->toBeFalse();
    });

    test('menu item can have custom CSS class', function (): void {
        $item = MenuItem::factory()->withCssClass('btn btn-primary')->create();

        expect($item->css_class)->toBe('btn btn-primary');
    });

    test('menu item getDisplayLabel returns label or fallback', function (): void {
        $menu = Menu::factory()->create();

        $withLabel = MenuItem::factory()->forMenu($menu)->create(['label' => 'Custom Label']);
        expect($withLabel->getDisplayLabel())->toBe('Custom Label');

        $noLabel = MenuItem::factory()->forMenu($menu)->create(['label' => '']);
        expect($noLabel->getDisplayLabel())->toBe('Untitled');
    });
});

describe('MenuService', function (): void {
    test('menu service returns items for location', function (): void {
        $menu = Menu::factory()->header()->create();
        MenuItem::factory()->forMenu($menu)->count(3)->create();

        $service = app(MenuService::class);
        $items = $service->getMenuItems('header');

        expect($items)->toHaveCount(3);
    });

    test('menu service returns empty collection for non-existent location', function (): void {
        $service = app(MenuService::class);
        $items = $service->getMenuItems('nonexistent');

        expect($items)->toBeEmpty();
    });

    test('menu service can clear cache', function (): void {
        $service = app(MenuService::class);

        // This should not throw
        $service->clearCache();
        $service->clearCache('header');

        expect(true)->toBeTrue();
    });

    test('menu service can get menu by location', function (): void {
        $menu = Menu::factory()->footer()->create(['name' => 'Footer']);

        $service = app(MenuService::class);
        $found = $service->getMenu('footer');

        expect($found)->not->toBeNull();
        expect($found->name)->toBe('Footer');
    });
});

describe('Menu Factory', function (): void {
    test('factory creates valid menu', function (): void {
        $menu = Menu::factory()->create();

        expect($menu->name)->not->toBeEmpty();
        expect($menu->location)->toBe('none');
    });

    test('factory header state works', function (): void {
        $menu = Menu::factory()->header()->create();

        expect($menu->location)->toBe('header');
    });

    test('factory footer state works', function (): void {
        $menu = Menu::factory()->footer()->create();

        expect($menu->location)->toBe('footer');
    });

    test('factory mobile state works', function (): void {
        $menu = Menu::factory()->mobile()->create();

        expect($menu->location)->toBe('mobile');
    });
});

describe('MenuItem Factory', function (): void {
    test('factory creates valid menu item', function (): void {
        $item = MenuItem::factory()->create();

        expect($item->label)->not->toBeEmpty();
        expect($item->menu_id)->not->toBeNull();
    });

    test('factory customLink state works', function (): void {
        $item = MenuItem::factory()->customLink('https://test.com', 'Test')->create();

        expect($item->url)->toBe('https://test.com');
        expect($item->label)->toBe('Test');
    });

    test('factory openInNewTab state works', function (): void {
        $item = MenuItem::factory()->openInNewTab()->create();

        expect($item->target)->toBe('_blank');
    });
});

describe('Menu Resource', function (): void {
    test('admin can access menus list', function (): void {
        $this->actingAs($this->admin)
            ->get('/admin/menus')
            ->assertSuccessful();
    })->skip('Requires Shield configuration - run shield:generate after deployment');

    test('admin can create a menu', function (): void {
        $this->actingAs($this->admin)
            ->get('/admin/menus/create')
            ->assertSuccessful();
    })->skip('Requires Shield configuration - run shield:generate after deployment');
});
