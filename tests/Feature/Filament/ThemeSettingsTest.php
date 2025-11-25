<?php

declare(strict_types=1);

use App\Filament\Pages\ThemeSettings;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('public');
});

test('admin can access theme settings page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(ThemeSettings::getUrl())
        ->assertSuccessful();
});

test('admin can update primary color', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    livewire(ThemeSettings::class)
        ->fillForm([
            'primary_color' => '#3b82f6',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('theme.primary_color'))->toBe('#3b82f6');
});

test('admin can update secondary color', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    livewire(ThemeSettings::class)
        ->fillForm([
            'secondary_color' => '#10b981',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('theme.secondary_color'))->toBe('#10b981');
});

test('admin can upload logo', function (): void {
    $admin = User::factory()->admin()->create();
    $logo = UploadedFile::fake()->image('logo.png', 200, 60);

    $this->actingAs($admin);

    livewire(ThemeSettings::class)
        ->fillForm([
            'logo' => $logo,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('theme.logo'))->not->toBeNull();
});

test('admin can upload favicon', function (): void {
    $admin = User::factory()->admin()->create();
    // Use PNG for favicon as .ico files have special handling
    $favicon = UploadedFile::fake()->image('favicon.png', 32, 32);

    $this->actingAs($admin);

    livewire(ThemeSettings::class)
        ->fillForm([
            'favicon' => $favicon,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('theme.favicon'))->not->toBeNull();
});

test('admin can update footer text', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    livewire(ThemeSettings::class)
        ->fillForm([
            'footer_text' => 'Copyright 2024 My Blog. All rights reserved.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('theme.footer_text'))->toBe('Copyright 2024 My Blog. All rights reserved.');
});

test('admin can reset theme to defaults', function (): void {
    $admin = User::factory()->admin()->create();

    // Set some custom values first
    Setting::set('theme.primary_color', '#ff0000', 'theme');
    Setting::set('theme.secondary_color', '#00ff00', 'theme');

    $this->actingAs($admin);

    livewire(ThemeSettings::class)
        ->call('resetToDefaults')
        ->assertHasNoFormErrors();

    // Should be reset to defaults
    expect(Setting::get('theme.primary_color'))->toBe('#3b82f6');
});

test('non-admin cannot access theme settings', function (): void {
    $author = User::factory()->create();
    $author->assignRole('author');

    $this->actingAs($author)
        ->get(ThemeSettings::getUrl())
        ->assertForbidden();
});

test('theme settings loads existing values', function (): void {
    $admin = User::factory()->admin()->create();

    Setting::set('theme.primary_color', '#dc2626', 'theme');
    Setting::set('theme.footer_text', 'Custom Footer', 'theme');

    $this->actingAs($admin);

    livewire(ThemeSettings::class)
        ->assertFormSet([
            'primary_color' => '#dc2626',
            'footer_text' => 'Custom Footer',
        ]);
});
