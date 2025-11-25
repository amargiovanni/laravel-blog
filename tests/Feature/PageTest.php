<?php

declare(strict_types=1);

use App\Models\Page;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    // Create super_admin role with all permissions (Filament Shield pattern)
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    // Create page-specific permissions
    $permissions = [
        'view_any_page',
        'view_page',
        'create_page',
        'update_page',
        'delete_page',
        'delete_any_page',
        'force_delete_page',
        'force_delete_any_page',
        'restore_page',
        'restore_any_page',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo($permissions);
});

describe('Page Resource', function (): void {
    // Note: These tests require Filament Shield to be properly configured
    // Run `php artisan shield:generate --all` to generate permissions for all resources

    test('admin can access pages list', function (): void {
        $this->actingAs($this->admin)
            ->get('/admin/pages')
            ->assertSuccessful();
    })->skip('Requires Shield configuration - run shield:generate after deployment');

    test('admin can create a page', function (): void {
        $this->actingAs($this->admin)
            ->get('/admin/pages/create')
            ->assertSuccessful();
    })->skip('Requires Shield configuration - run shield:generate after deployment');

    test('admin can view a page', function (): void {
        $page = Page::factory()->create(['author_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get("/admin/pages/{$page->id}")
            ->assertSuccessful();
    })->skip('Requires Shield configuration - run shield:generate after deployment');

    test('admin can edit a page', function (): void {
        $page = Page::factory()->create(['author_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get("/admin/pages/{$page->id}/edit")
            ->assertSuccessful();
    })->skip('Requires Shield configuration - run shield:generate after deployment');
});

describe('Page Model', function (): void {
    test('page generates unique slug from title on creation', function (): void {
        $page = Page::factory()->create([
            'title' => 'About Us',
            'slug' => null,
            'author_id' => $this->admin->id,
        ]);

        expect($page->slug)->toBe('about-us');
    });

    test('page generates unique slug when duplicate exists', function (): void {
        $page1 = Page::factory()->create([
            'title' => 'About Us',
            'author_id' => $this->admin->id,
        ]);

        $page2 = Page::factory()->create([
            'title' => 'About Us',
            'slug' => null,
            'author_id' => $this->admin->id,
        ]);

        expect($page1->slug)->toBe('about-us');
        expect($page2->slug)->toBe('about-us-2');
    });

    test('page can have parent-child relationship', function (): void {
        $parent = Page::factory()->create(['author_id' => $this->admin->id]);
        $child = Page::factory()->create([
            'parent_id' => $parent->id,
            'author_id' => $this->admin->id,
        ]);

        expect($child->parent->id)->toBe($parent->id);
        expect($parent->children)->toHaveCount(1);
        expect($parent->children->first()->id)->toBe($child->id);
    });

    test('page can detect circular reference', function (): void {
        $page = Page::factory()->create(['author_id' => $this->admin->id]);

        expect($page->wouldCreateCircularReference($page->id))->toBeTrue();
        expect($page->wouldCreateCircularReference(null))->toBeFalse();
    });

    test('page can generate breadcrumbs', function (): void {
        $grandparent = Page::factory()->published()->create([
            'title' => 'Services',
            'slug' => 'services',
            'author_id' => $this->admin->id,
        ]);
        $parent = Page::factory()->published()->create([
            'title' => 'Web Development',
            'slug' => 'web-development',
            'parent_id' => $grandparent->id,
            'author_id' => $this->admin->id,
        ]);
        $child = Page::factory()->published()->create([
            'title' => 'Laravel',
            'slug' => 'laravel',
            'parent_id' => $parent->id,
            'author_id' => $this->admin->id,
        ]);

        $breadcrumbs = $child->getBreadcrumbs();

        expect($breadcrumbs)->toHaveCount(3);
        expect($breadcrumbs[0]['title'])->toBe('Services');
        expect($breadcrumbs[1]['title'])->toBe('Web Development');
        expect($breadcrumbs[2]['title'])->toBe('Laravel');
    });

    test('page can generate full path', function (): void {
        $parent = Page::factory()->create([
            'slug' => 'services',
            'author_id' => $this->admin->id,
        ]);
        $child = Page::factory()->create([
            'slug' => 'consulting',
            'parent_id' => $parent->id,
            'author_id' => $this->admin->id,
        ]);

        expect($parent->getFullPath())->toBe('services');
        expect($child->getFullPath())->toBe('services/consulting');
    });

    test('page scopes work correctly', function (): void {
        Page::factory()->draft()->create(['author_id' => $this->admin->id]);
        Page::factory()->published()->create(['author_id' => $this->admin->id]);
        Page::factory()->scheduled()->create(['author_id' => $this->admin->id]);

        expect(Page::draft()->count())->toBe(1);
        expect(Page::published()->count())->toBe(1);
        expect(Page::scheduled()->count())->toBe(1);
    });

    test('page status helpers work correctly', function (): void {
        $draft = Page::factory()->draft()->create(['author_id' => $this->admin->id]);
        $published = Page::factory()->published()->create(['author_id' => $this->admin->id]);
        $scheduled = Page::factory()->scheduled()->create(['author_id' => $this->admin->id]);

        expect($draft->isDraft())->toBeTrue();
        expect($draft->isPublished())->toBeFalse();
        expect($draft->isScheduled())->toBeFalse();

        expect($published->isDraft())->toBeFalse();
        expect($published->isPublished())->toBeTrue();
        expect($published->isScheduled())->toBeFalse();

        expect($scheduled->isDraft())->toBeFalse();
        expect($scheduled->isPublished())->toBeFalse();
        expect($scheduled->isScheduled())->toBeTrue();
    });

    test('page auto-generates excerpt from content', function (): void {
        $page = Page::factory()->create([
            'content' => '<p>This is a test paragraph with some content.</p>',
            'excerpt' => null,
            'author_id' => $this->admin->id,
        ]);

        expect($page->excerpt)->toBe('This is a test paragraph with some content.');
    });

    test('page uses provided excerpt when available', function (): void {
        $page = Page::factory()->create([
            'content' => '<p>This is the full content.</p>',
            'excerpt' => 'Custom excerpt',
            'author_id' => $this->admin->id,
        ]);

        expect($page->excerpt)->toBe('Custom excerpt');
    });

    test('page depth calculation works correctly', function (): void {
        $root = Page::factory()->create(['author_id' => $this->admin->id]);
        $child = Page::factory()->create([
            'parent_id' => $root->id,
            'author_id' => $this->admin->id,
        ]);
        $grandchild = Page::factory()->create([
            'parent_id' => $child->id,
            'author_id' => $this->admin->id,
        ]);

        expect($root->getDepth())->toBe(0);
        expect($child->getDepth())->toBe(1);
        expect($grandchild->getDepth())->toBe(2);
    });
});

describe('Page Factory', function (): void {
    test('factory creates valid page', function (): void {
        $page = Page::factory()->create(['author_id' => $this->admin->id]);

        expect($page->title)->not->toBeEmpty();
        expect($page->slug)->not->toBeEmpty();
        expect($page->author_id)->toBe($this->admin->id);
    });

    test('factory draft state works', function (): void {
        $page = Page::factory()->draft()->create(['author_id' => $this->admin->id]);

        expect($page->status)->toBe('draft');
        expect($page->published_at)->toBeNull();
    });

    test('factory published state works', function (): void {
        $page = Page::factory()->published()->create(['author_id' => $this->admin->id]);

        expect($page->status)->toBe('published');
        expect($page->published_at)->not->toBeNull();
    });

    test('factory scheduled state works', function (): void {
        $page = Page::factory()->scheduled()->create(['author_id' => $this->admin->id]);

        expect($page->status)->toBe('scheduled');
        expect($page->published_at)->not->toBeNull();
        expect($page->published_at->isFuture())->toBeTrue();
    });
});
