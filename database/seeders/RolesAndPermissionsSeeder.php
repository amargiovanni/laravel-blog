<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions using Shield's naming convention
        $permissions = [
            // Posts (Shield format)
            'view_post', 'view_any_post', 'create_post', 'update_post',
            'delete_post', 'delete_any_post', 'restore_post', 'restore_any_post',
            'replicate_post', 'reorder_post', 'force_delete_post', 'force_delete_any_post',

            // Categories (Shield format)
            'view_category', 'view_any_category', 'create_category', 'update_category',
            'delete_category', 'delete_any_category', 'restore_category', 'restore_any_category',
            'replicate_category', 'reorder_category', 'force_delete_category', 'force_delete_any_category',

            // Tags (Shield format)
            'view_tag', 'view_any_tag', 'create_tag', 'update_tag',
            'delete_tag', 'delete_any_tag', 'restore_tag', 'restore_any_tag',
            'replicate_tag', 'reorder_tag', 'force_delete_tag', 'force_delete_any_tag',

            // Media (Shield format)
            'view_media', 'view_any_media', 'create_media', 'update_media',
            'delete_media', 'delete_any_media', 'restore_media', 'restore_any_media',
            'replicate_media', 'reorder_media', 'force_delete_media', 'force_delete_any_media',

            // Users (Shield format)
            'view_user', 'view_any_user', 'create_user', 'update_user',
            'delete_user', 'delete_any_user', 'restore_user', 'restore_any_user',
            'replicate_user', 'reorder_user', 'force_delete_user', 'force_delete_any_user',

            // Roles (Shield format)
            'view_role', 'view_any_role', 'create_role', 'update_role',
            'delete_role', 'delete_any_role',

            // Legacy permissions for backward compatibility
            'edit own posts',
            'delete own posts',
            'publish posts',
            'view comments',
            'create comments',
            'moderate comments',
            'delete comments',
            'manage settings',
            'manage theme settings',
            'manage geo settings',
            'view activity log',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editor->syncPermissions([
            // Posts
            'view_post', 'view_any_post', 'create_post', 'update_post', 'delete_post', 'publish posts',
            // Categories
            'view_category', 'view_any_category', 'create_category', 'update_category', 'delete_category',
            // Tags
            'view_tag', 'view_any_tag', 'create_tag', 'update_tag', 'delete_tag',
            // Comments
            'view comments', 'moderate comments', 'delete comments',
            // Media
            'view_media', 'view_any_media', 'create_media', 'update_media', 'delete_media',
            // Activity
            'view activity log',
        ]);

        $author = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);
        $author->syncPermissions([
            // Posts
            'view_post', 'view_any_post', 'create_post', 'edit own posts', 'delete own posts',
            // Categories/Tags
            'view_category', 'view_any_category', 'view_tag', 'view_any_tag', 'create_tag',
            // Comments
            'view comments', 'create comments',
            // Media
            'view_media', 'view_any_media', 'create_media',
        ]);

        $subscriber = Role::firstOrCreate(['name' => 'subscriber', 'guard_name' => 'web']);
        $subscriber->syncPermissions([
            'view_post', 'view_any_post',
            'view_category', 'view_any_category',
            'view_tag', 'view_any_tag',
            'create comments',
        ]);
    }
}
