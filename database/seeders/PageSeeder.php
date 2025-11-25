<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first() ?? User::factory()->create();

        // Create common static pages
        $aboutPage = Page::factory()->published()->create([
            'title' => 'About Us',
            'slug' => 'about',
            'content' => '<p>Welcome to our blog! We are passionate about sharing knowledge and insights with our readers.</p><p>Our team consists of experienced writers who are dedicated to creating high-quality content.</p>',
            'author_id' => $admin->id,
            'meta_title' => 'About Us - Learn More About Our Blog',
            'meta_description' => 'Discover who we are, our mission, and what drives us to create great content for our readers.',
        ]);

        Page::factory()->published()->create([
            'title' => 'Our Team',
            'slug' => 'team',
            'parent_id' => $aboutPage->id,
            'content' => '<p>Meet our talented team of writers and editors.</p>',
            'author_id' => $admin->id,
            'sort_order' => 1,
        ]);

        Page::factory()->published()->create([
            'title' => 'Our History',
            'slug' => 'history',
            'parent_id' => $aboutPage->id,
            'content' => '<p>Learn about our journey and how we got started.</p>',
            'author_id' => $admin->id,
            'sort_order' => 2,
        ]);

        Page::factory()->published()->create([
            'title' => 'Contact',
            'slug' => 'contact',
            'content' => '<p>Get in touch with us! We would love to hear from you.</p><p>Email: contact@example.com</p>',
            'author_id' => $admin->id,
            'meta_title' => 'Contact Us',
            'meta_description' => 'Have questions? Contact us and we will get back to you as soon as possible.',
        ]);

        Page::factory()->published()->create([
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'content' => '<h2>Privacy Policy</h2><p>This privacy policy outlines how we collect, use, and protect your personal information.</p>',
            'author_id' => $admin->id,
            'template' => 'full-width',
            'meta_title' => 'Privacy Policy',
            'meta_description' => 'Read our privacy policy to understand how we handle your data.',
        ]);

        Page::factory()->published()->create([
            'title' => 'Terms of Service',
            'slug' => 'terms-of-service',
            'content' => '<h2>Terms of Service</h2><p>By using our website, you agree to the following terms and conditions.</p>',
            'author_id' => $admin->id,
            'template' => 'full-width',
            'meta_title' => 'Terms of Service',
            'meta_description' => 'Review our terms of service before using our website.',
        ]);

        // Create a draft page
        Page::factory()->draft()->create([
            'title' => 'Coming Soon',
            'slug' => 'coming-soon',
            'content' => '<p>This page is under construction.</p>',
            'author_id' => $admin->id,
        ]);
    }
}
