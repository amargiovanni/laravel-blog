<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Blog Name & Description
    |--------------------------------------------------------------------------
    |
    | The name and description of the blog used in meta tags, feeds, etc.
    |
    */
    'name' => env('BLOG_NAME', config('app.name')),
    'description' => env('BLOG_DESCRIPTION', 'A Laravel-powered blog'),

    /*
    |--------------------------------------------------------------------------
    | Posts Settings
    |--------------------------------------------------------------------------
    |
    | Configuration options for blog posts.
    |
    */
    'posts' => [
        'per_page' => 10,
        'excerpt_length' => 200,
        'auto_save_interval' => 30, // seconds
        'allowed_statuses' => ['draft', 'scheduled', 'published'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Categories Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for hierarchical categories.
    |
    */
    'categories' => [
        'max_nesting_depth' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Comments Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the comment system.
    |
    */
    'comments' => [
        'require_moderation' => true,
        'allow_guest' => true,
        'max_reply_depth' => 2,
        'rate_limit' => [
            'max_attempts' => 3,
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the media library and image processing.
    |
    */
    'media' => [
        'disk' => env('BLOG_MEDIA_DISK', 'public'),
        'max_upload_size' => 10240, // KB
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'sizes' => [
            'thumbnail' => ['width' => 150, 'height' => 150],
            'medium' => ['width' => 600, 'height' => 400],
            'large' => ['width' => 1200, 'height' => 800],
        ],
        'optimize' => true,
        'convert_to_webp' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Settings
    |--------------------------------------------------------------------------
    |
    | Default SEO configuration values.
    |
    */
    'seo' => [
        'default_meta_title' => env('BLOG_NAME', config('app.name')),
        'default_meta_description' => env('BLOG_DESCRIPTION', 'A Laravel-powered blog'),
        'og_type' => 'article',
        'twitter_card' => 'summary_large_image',
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Settings
    |--------------------------------------------------------------------------
    |
    | Frontend theme configuration.
    |
    */
    'theme' => [
        'default' => 'default',
        'path' => resource_path('views/themes'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Log Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for activity logging.
    |
    */
    'activity_log' => [
        'retention_days' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the search functionality.
    |
    */
    'search' => [
        'min_query_length' => 2,
        'max_results' => 50,
        'autocomplete_limit' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Sharing
    |--------------------------------------------------------------------------
    |
    | Platforms enabled for social sharing.
    |
    */
    'social' => [
        'platforms' => ['twitter', 'facebook', 'linkedin', 'copy'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feed Settings
    |--------------------------------------------------------------------------
    |
    | RSS/Atom feed configuration.
    |
    */
    'feed' => [
        'items' => 20,
    ],
];
