<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Create Header Navigation Menu
        $headerMenu = Menu::factory()->header()->create(['name' => 'Main Navigation']);

        // Add Home link
        MenuItem::factory()->forMenu($headerMenu)->create([
            'label' => 'Home',
            'url' => '/',
            'sort_order' => 0,
        ]);

        // Add About page link if exists
        $aboutPage = Page::where('slug', 'about')->first();
        if ($aboutPage) {
            MenuItem::factory()->forMenu($headerMenu)->create([
                'label' => 'About',
                'linkable_type' => Page::class,
                'linkable_id' => $aboutPage->id,
                'sort_order' => 1,
            ]);
        }

        // Add Contact page link if exists
        $contactPage = Page::where('slug', 'contact')->first();
        if ($contactPage) {
            MenuItem::factory()->forMenu($headerMenu)->create([
                'label' => 'Contact',
                'linkable_type' => Page::class,
                'linkable_id' => $contactPage->id,
                'sort_order' => 2,
            ]);
        }

        // Create Footer Menu
        $footerMenu = Menu::factory()->footer()->create(['name' => 'Footer Links']);

        // Add Privacy Policy link if exists
        $privacyPage = Page::where('slug', 'privacy-policy')->first();
        if ($privacyPage) {
            MenuItem::factory()->forMenu($footerMenu)->create([
                'label' => 'Privacy Policy',
                'linkable_type' => Page::class,
                'linkable_id' => $privacyPage->id,
                'sort_order' => 0,
            ]);
        }

        // Add Terms of Service link if exists
        $termsPage = Page::where('slug', 'terms-of-service')->first();
        if ($termsPage) {
            MenuItem::factory()->forMenu($footerMenu)->create([
                'label' => 'Terms of Service',
                'linkable_type' => Page::class,
                'linkable_id' => $termsPage->id,
                'sort_order' => 1,
            ]);
        }

        // Create Mobile Menu (same as header initially)
        $mobileMenu = Menu::factory()->mobile()->create(['name' => 'Mobile Navigation']);

        MenuItem::factory()->forMenu($mobileMenu)->create([
            'label' => 'Home',
            'url' => '/',
            'sort_order' => 0,
        ]);

        if ($aboutPage) {
            MenuItem::factory()->forMenu($mobileMenu)->create([
                'label' => 'About',
                'linkable_type' => Page::class,
                'linkable_id' => $aboutPage->id,
                'sort_order' => 1,
            ]);
        }

        if ($contactPage) {
            MenuItem::factory()->forMenu($mobileMenu)->create([
                'label' => 'Contact',
                'linkable_type' => Page::class,
                'linkable_id' => $contactPage->id,
                'sort_order' => 2,
            ]);
        }
    }
}
