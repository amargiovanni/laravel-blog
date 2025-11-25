<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\WidgetInstance;
use Illuminate\Database\Seeder;

class WidgetSeeder extends Seeder
{
    public function run(): void
    {
        // Primary Sidebar widgets
        WidgetInstance::factory()->search()->forArea('primary_sidebar')->create([
            'title' => 'Search',
            'sort_order' => 0,
        ]);

        WidgetInstance::factory()->recentPosts()->forArea('primary_sidebar')->create([
            'title' => 'Recent Posts',
            'sort_order' => 1,
        ]);

        WidgetInstance::factory()->categories()->forArea('primary_sidebar')->create([
            'title' => 'Categories',
            'sort_order' => 2,
        ]);

        WidgetInstance::factory()->tags()->forArea('primary_sidebar')->create([
            'title' => 'Tags',
            'sort_order' => 3,
        ]);

        // Footer Column 1
        WidgetInstance::factory()->customHtml('<p>Your blog description here.</p>')->forArea('footer_1')->create([
            'title' => 'About Us',
            'sort_order' => 0,
        ]);

        // Footer Column 2
        WidgetInstance::factory()->recentPosts()->forArea('footer_2')->create([
            'title' => 'Latest Posts',
            'settings' => ['count' => 3, 'show_date' => false, 'show_thumbnail' => false],
            'sort_order' => 0,
        ]);

        // Footer Column 3
        WidgetInstance::factory()->archives()->forArea('footer_3')->create([
            'title' => 'Archives',
            'settings' => ['type' => 'monthly', 'show_count' => true, 'limit' => 6],
            'sort_order' => 0,
        ]);
    }
}
