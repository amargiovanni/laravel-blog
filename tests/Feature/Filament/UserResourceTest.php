<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin can access users list page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(UserResource::getUrl('index'))
        ->assertSuccessful();
});

test('admin can see users in table', function (): void {
    $admin = User::factory()->admin()->create();
    $users = User::factory(3)->create();

    $this->actingAs($admin);

    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users);
});

test('admin can create a user', function (): void {
    Notification::fake();

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(UserResource::getUrl('create'))
        ->assertSuccessful();

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();

    // Welcome notification should be sent
    Notification::assertSentTo(
        User::where('email', 'test@example.com')->first(),
        WelcomeNotification::class
    );
});

test('admin can edit a user', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->get(UserResource::getUrl('edit', ['record' => $user]))
        ->assertSuccessful();

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'Updated User Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->name)->toBe('Updated User Name');
});

test('admin can delete a user', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin);

    livewire(ListUsers::class)
        ->callTableAction('delete', $user);

    expect(User::find($user->id))->toBeNull();
});

test('last admin cannot be deleted', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    livewire(ListUsers::class)
        ->callTableAction('delete', $admin)
        ->assertNotified();

    expect(User::find($admin->id))->not->toBeNull();
});

test('admin can deactivate a user', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['is_active' => true]);

    $this->actingAs($admin);

    livewire(ListUsers::class)
        ->callTableAction('deactivate', $user);

    expect($user->fresh()->is_active)->toBeFalse();
});

test('admin can activate a user', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['is_active' => false]);

    $this->actingAs($admin);

    livewire(ListUsers::class)
        ->callTableAction('activate', $user);

    expect($user->fresh()->is_active)->toBeTrue();
});

test('admin can assign roles to a user', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $editorRole = Spatie\Permission\Models\Role::where('name', 'editor')->first();

    $this->actingAs($admin);

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'roles' => [$editorRole->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->hasRole('editor'))->toBeTrue();
});

test('users can be filtered by role', function (): void {
    $admin = User::factory()->admin()->create();
    $editor = User::factory()->create();
    $editor->assignRole('editor');
    $author = User::factory()->create();
    $author->assignRole('author');

    $this->actingAs($admin);

    livewire(ListUsers::class)
        ->filterTable('role', 'editor')
        ->assertCanSeeTableRecords([$editor])
        ->assertCanNotSeeTableRecords([$author]);
});

test('users can be filtered by status', function (): void {
    $admin = User::factory()->admin()->create();
    $activeUser = User::factory()->create(['is_active' => true]);
    $inactiveUser = User::factory()->create(['is_active' => false]);

    $this->actingAs($admin);

    livewire(ListUsers::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords([$admin, $activeUser])
        ->assertCanNotSeeTableRecords([$inactiveUser]);
});

test('users can be searched by name', function (): void {
    $admin = User::factory()->admin()->create();
    $searchTarget = User::factory()->create(['name' => 'John Doe']);
    $other = User::factory()->create(['name' => 'Jane Smith']);

    $this->actingAs($admin);

    livewire(ListUsers::class)
        ->searchTable('John Doe')
        ->assertCanSeeTableRecords([$searchTarget])
        ->assertCanNotSeeTableRecords([$other]);
});

test('user email is required', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'required']);
});

test('user email must be unique', function (): void {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'existing@example.com']);

    $this->actingAs($admin);

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});
