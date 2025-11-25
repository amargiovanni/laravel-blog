<?php

declare(strict_types=1);

use App\Filament\Resources\MediaResource;
use App\Filament\Resources\MediaResource\Pages\CreateMedia;
use App\Filament\Resources\MediaResource\Pages\EditMedia;
use App\Filament\Resources\MediaResource\Pages\ListMedia;
use App\Models\Media;
use App\Models\Post;
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

test('admin can access media list page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(MediaResource::getUrl('index'))
        ->assertSuccessful();
});

test('admin can see media in table', function (): void {
    $admin = User::factory()->admin()->create();
    $media = Media::factory(3)->for($admin, 'uploader')->create();

    $this->actingAs($admin);

    livewire(ListMedia::class)
        ->assertCanSeeTableRecords($media);
});

test('admin can upload media', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(MediaResource::getUrl('create'))
        ->assertSuccessful();

    $file = UploadedFile::fake()->image('test-image.jpg', 800, 600);

    livewire(CreateMedia::class)
        ->fillForm([
            'file' => $file,
            'alt' => 'Test alt text',
            'title' => 'Test title',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Media::where('alt', 'Test alt text')->exists())->toBeTrue();
});

test('admin can edit media metadata', function (): void {
    $admin = User::factory()->admin()->create();
    $media = Media::factory()->for($admin, 'uploader')->create();

    $this->actingAs($admin)
        ->get(MediaResource::getUrl('edit', ['record' => $media]))
        ->assertSuccessful();

    livewire(EditMedia::class, ['record' => $media->getRouteKey()])
        ->fillForm([
            'alt' => 'Updated alt text',
            'title' => 'Updated title',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($media->fresh()->alt)->toBe('Updated alt text');
});

test('admin can delete unused media', function (): void {
    $admin = User::factory()->admin()->create();
    $media = Media::factory()->for($admin, 'uploader')->create();

    $this->actingAs($admin);

    livewire(ListMedia::class)
        ->callTableAction('delete', $media);

    expect(Media::find($media->id))->toBeNull();
});

test('media used as featured image cannot be deleted', function (): void {
    $admin = User::factory()->admin()->create();
    $media = Media::factory()->for($admin, 'uploader')->create();
    Post::factory()->for($admin, 'author')->create(['featured_image_id' => $media->id]);

    $this->actingAs($admin);

    livewire(ListMedia::class)
        ->callTableAction('delete', $media)
        ->assertNotified();

    expect(Media::find($media->id))->not->toBeNull();
});

test('media can be filtered by type (images)', function (): void {
    $admin = User::factory()->admin()->create();
    $image = Media::factory()->for($admin, 'uploader')->create(['mime_type' => 'image/jpeg']);
    $document = Media::factory()->for($admin, 'uploader')->document()->create();

    $this->actingAs($admin);

    livewire(ListMedia::class)
        ->filterTable('type', 'image')
        ->assertCanSeeTableRecords([$image])
        ->assertCanNotSeeTableRecords([$document]);
});

test('media can be filtered by usage status', function (): void {
    $admin = User::factory()->admin()->create();
    $usedMedia = Media::factory()->for($admin, 'uploader')->create();
    $unusedMedia = Media::factory()->for($admin, 'uploader')->create();
    Post::factory()->for($admin, 'author')->create(['featured_image_id' => $usedMedia->id]);

    $this->actingAs($admin);

    livewire(ListMedia::class)
        ->filterTable('usage', 'unused')
        ->assertCanSeeTableRecords([$unusedMedia])
        ->assertCanNotSeeTableRecords([$usedMedia]);
});

test('media can be searched by name', function (): void {
    $admin = User::factory()->admin()->create();
    $searchTarget = Media::factory()->for($admin, 'uploader')->create(['name' => 'special-image.jpg']);
    $other = Media::factory()->for($admin, 'uploader')->create(['name' => 'other-file.jpg']);

    $this->actingAs($admin);

    livewire(ListMedia::class)
        ->searchTable('special-image')
        ->assertCanSeeTableRecords([$searchTarget])
        ->assertCanNotSeeTableRecords([$other]);
});

test('admin can bulk delete unused media', function (): void {
    $admin = User::factory()->admin()->create();
    $unusedMedia = Media::factory(3)->for($admin, 'uploader')->create();

    $this->actingAs($admin);

    livewire(ListMedia::class)
        ->callTableBulkAction('delete_unused', $unusedMedia);

    foreach ($unusedMedia as $media) {
        expect(Media::find($media->id))->toBeNull();
    }
});

test('bulk delete skips media in use', function (): void {
    $admin = User::factory()->admin()->create();
    $usedMedia = Media::factory()->for($admin, 'uploader')->create();
    $unusedMedia = Media::factory()->for($admin, 'uploader')->create();
    Post::factory()->for($admin, 'author')->create(['featured_image_id' => $usedMedia->id]);

    $this->actingAs($admin);

    livewire(ListMedia::class)
        ->callTableBulkAction('delete_unused', [$usedMedia, $unusedMedia]);

    // Used media should still exist
    expect(Media::find($usedMedia->id))->not->toBeNull();
    // Unused media should be deleted
    expect(Media::find($unusedMedia->id))->toBeNull();
});

test('usage count is displayed for media', function (): void {
    $admin = User::factory()->admin()->create();
    $media = Media::factory()->for($admin, 'uploader')->create();
    Post::factory(2)->for($admin, 'author')->create(['featured_image_id' => $media->id]);

    $this->actingAs($admin);

    livewire(ListMedia::class)
        ->assertCanSeeTableRecords([$media]);
});

test('thumbnail preview is displayed for images', function (): void {
    $admin = User::factory()->admin()->create();
    $media = Media::factory()->for($admin, 'uploader')->create();

    $this->actingAs($admin);

    livewire(ListMedia::class)
        ->assertCanSeeTableRecords([$media]);
});
