<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('guests cannot access admin panel', function (): void {
    $this->get('/admin')
        ->assertRedirect('/admin/login');
});

test('subscribers cannot access admin panel', function (): void {
    $user = User::factory()->subscriber()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

test('authors can access admin panel', function (): void {
    $user = User::factory()->author()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});

test('editors can access admin panel', function (): void {
    $user = User::factory()->editor()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});

test('admins can access admin panel', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});
