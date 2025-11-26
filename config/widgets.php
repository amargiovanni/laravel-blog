<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Widget Areas
    |--------------------------------------------------------------------------
    |
    | Define the available widget areas in your theme. Each area will be
    | available in the Widgets admin interface and can contain widgets.
    |
    */
    'areas' => [
        'primary_sidebar' => [
            'name' => 'Primary Sidebar',
            'description' => 'Main sidebar area on blog pages',
        ],
        'footer_1' => [
            'name' => 'Footer Column 1',
            'description' => 'First column in the footer',
        ],
        'footer_2' => [
            'name' => 'Footer Column 2',
            'description' => 'Second column in the footer',
        ],
        'footer_3' => [
            'name' => 'Footer Column 3',
            'description' => 'Third column in the footer',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Types
    |--------------------------------------------------------------------------
    |
    | Register all available widget types. Each type should have a class
    | that extends App\Widgets\BaseWidget.
    |
    */
    'types' => [
        'search' => [
            'name' => 'Search',
            'description' => 'A search form',
            'class' => App\Widgets\SearchWidget::class,
        ],
        'recent_posts' => [
            'name' => 'Recent Posts',
            'description' => 'Shows a list of recent posts',
            'class' => App\Widgets\RecentPostsWidget::class,
        ],
        'categories' => [
            'name' => 'Categories',
            'description' => 'Shows a list of categories with post counts',
            'class' => App\Widgets\CategoriesWidget::class,
        ],
        'tags' => [
            'name' => 'Tag Cloud',
            'description' => 'Shows tags with varying sizes based on usage',
            'class' => App\Widgets\TagsWidget::class,
        ],
        'archives' => [
            'name' => 'Archives',
            'description' => 'Shows monthly or yearly archives',
            'class' => App\Widgets\ArchivesWidget::class,
        ],
        'custom_html' => [
            'name' => 'Custom HTML',
            'description' => 'Custom HTML or text content',
            'class' => App\Widgets\CustomHtmlWidget::class,
        ],
        'newsletter' => [
            'name' => 'Newsletter',
            'description' => 'Newsletter subscription form',
            'class' => App\Widgets\NewsletterWidget::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Widget output caching configuration.
    |
    */
    'cache' => [
        'enabled' => env('WIDGETS_CACHE_ENABLED', true),
        'ttl' => env('WIDGETS_CACHE_TTL', 3600), // 1 hour
    ],
];
