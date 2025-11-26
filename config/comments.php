<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Comments Enabled
    |--------------------------------------------------------------------------
    |
    | Globally enable or disable comments across the entire site. When disabled,
    | no comment forms will be displayed, regardless of per-post settings.
    |
    */
    'enabled' => env('COMMENTS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Moderation Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, all new comments require admin approval before being visible.
    | When disabled, comments are auto-approved and appear immediately.
    |
    */
    'require_moderation' => env('COMMENTS_REQUIRE_MODERATION', true),

    /*
    |--------------------------------------------------------------------------
    | Auto-Close After Days
    |--------------------------------------------------------------------------
    |
    | Automatically disable comments on posts older than this many days.
    | Set to null or 0 to never auto-close comments.
    |
    */
    'auto_close_days' => env('COMMENTS_AUTO_CLOSE_DAYS', null),

    /*
    |--------------------------------------------------------------------------
    | Max Thread Depth
    |--------------------------------------------------------------------------
    |
    | Maximum depth for nested replies. Replies deeper than this will be
    | flattened to the maximum level for readability.
    |
    */
    'max_depth' => env('COMMENTS_MAX_DEPTH', 3),

    /*
    |--------------------------------------------------------------------------
    | Content Length Limits
    |--------------------------------------------------------------------------
    |
    | Minimum and maximum character limits for comment content.
    |
    */
    'min_length' => 3,
    'max_length' => 2000,

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Number of comments allowed per minute from the same IP address.
    |
    */
    'rate_limit' => 5,

    /*
    |--------------------------------------------------------------------------
    | Auto-Hold Links
    |--------------------------------------------------------------------------
    |
    | Automatically hold comments containing links for moderation review,
    | even when moderation is disabled.
    |
    */
    'auto_hold_links' => env('COMMENTS_AUTO_HOLD_LINKS', true),

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure who receives notifications about new comments.
    |
    */
    'notifications' => [
        'notify_author' => env('COMMENTS_NOTIFY_AUTHOR', true),
        'notify_admin' => env('COMMENTS_NOTIFY_ADMIN', true),
        'admin_email' => env('COMMENTS_ADMIN_EMAIL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Gravatar
    |--------------------------------------------------------------------------
    |
    | Configure Gravatar avatar integration for commenters.
    |
    */
    'gravatar' => [
        'enabled' => true,
        'default' => 'mp', // Mystery person
        'size' => 48,
        'rating' => 'g',
    ],
];
