<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Revision;
use App\Models\Subscriber;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds with demo content.
     */
    public function run(): void
    {
        $this->command->info('🌱 Creating demo data...');

        // Create authors (users with author role)
        $this->command->info('Creating authors...');
        $authors = User::factory(3)->author()->create();

        // Create 10 categories (some with parent categories)
        $this->command->info('Creating 10 categories...');
        $parentCategories = Category::factory(6)->create();
        $childCategories = Category::factory(4)->create([
            'parent_id' => fn () => $parentCategories->random()->id,
        ]);
        $allCategories = $parentCategories->merge($childCategories);

        // Create 45 tags
        $this->command->info('Creating 45 tags...');
        $tags = Tag::factory(45)->create();

        // Create 20 posts with revisions and comments
        $this->command->info('Creating 20 posts with revisions and comments...');

        // 15 published posts
        $publishedPosts = Post::factory(15)
            ->published()
            ->withViews(fake()->numberBetween(10, 500))
            ->recycle($authors)
            ->create();

        // 3 draft posts
        $draftPosts = Post::factory(3)
            ->draft()
            ->recycle($authors)
            ->create();

        // 2 scheduled posts
        $scheduledPosts = Post::factory(2)
            ->scheduled()
            ->recycle($authors)
            ->create();

        $allPosts = $publishedPosts->merge($draftPosts)->merge($scheduledPosts);

        // Attach categories and tags to posts
        $allPosts->each(function (Post $post) use ($allCategories, $tags): void {
            // Attach 1-3 categories
            $post->categories()->attach(
                $allCategories->random(fake()->numberBetween(1, 3))->pluck('id')
            );

            // Attach 2-6 tags
            $post->tags()->attach(
                $tags->random(fake()->numberBetween(2, 6))->pluck('id')
            );
        });

        // Create revisions for posts (2-5 revisions per post)
        $this->command->info('Creating revisions for posts...');
        $allPosts->each(function (Post $post) use ($authors): void {
            $revisionCount = fake()->numberBetween(2, 5);
            for ($i = 1; $i <= $revisionCount; $i++) {
                Revision::factory()
                    ->forPost($post)
                    ->revisionNumber($i)
                    ->byUser($authors->random())
                    ->create([
                        'created_at' => $post->created_at->subDays($revisionCount - $i),
                    ]);
            }
        });

        // Create comments for published posts
        $this->command->info('Creating comments for posts...');
        $publishedPosts->each(function (Post $post): void {
            $commentCount = fake()->numberBetween(0, 8);

            if ($commentCount === 0) {
                return;
            }

            // Create root comments (various statuses)
            $approvedCount = (int) ceil($commentCount * 0.7);
            $pendingCount = (int) ceil($commentCount * 0.2);
            $otherCount = $commentCount - $approvedCount - $pendingCount;

            // Approved comments
            $rootComments = Comment::factory($approvedCount)
                ->approved()
                ->create(['post_id' => $post->id]);

            // Pending comments
            Comment::factory(max(1, $pendingCount))
                ->pending()
                ->create(['post_id' => $post->id]);

            // Some rejected/spam
            if ($otherCount > 0) {
                Comment::factory()
                    ->state(['status' => fake()->randomElement(['rejected', 'spam'])])
                    ->create(['post_id' => $post->id]);
            }

            // Add replies to some approved comments
            $rootComments->take(2)->each(function (Comment $parent): void {
                Comment::factory(fake()->numberBetween(1, 3))
                    ->approved()
                    ->replyTo($parent)
                    ->create();
            });
        });

        // Create 30 subscribers
        $this->command->info('Creating 30 subscribers...');
        Subscriber::factory(20)->verified()->create();
        Subscriber::factory(7)->create(); // Unverified
        Subscriber::factory(3)->unsubscribed()->create();

        // Create contact messages
        $this->command->info('Creating contact messages...');
        ContactMessage::factory(8)->read()->create();
        ContactMessage::factory(12)->unread()->create();

        $this->command->info('✅ Demo data created successfully!');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Categories', Category::count()],
                ['Tags', Tag::count()],
                ['Posts', Post::count()],
                ['Revisions', Revision::count()],
                ['Comments', Comment::count()],
                ['Subscribers', Subscriber::count()],
                ['Contact Messages', ContactMessage::count()],
            ]
        );
    }
}
