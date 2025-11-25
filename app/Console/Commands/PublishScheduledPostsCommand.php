<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all scheduled posts that are due';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $posts = Post::where('status', 'scheduled')
            ->where('published_at', '<=', now())
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No scheduled posts to publish.');

            return self::SUCCESS;
        }

        $count = 0;
        foreach ($posts as $post) {
            $post->update(['status' => 'published']);
            $count++;
            $this->line("Published: {$post->title}");
        }

        $this->info("Published {$count} post(s).");

        return self::SUCCESS;
    }
}
