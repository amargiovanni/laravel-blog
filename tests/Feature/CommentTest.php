<?php

declare(strict_types=1);

use App\Livewire\CommentForm;
use App\Livewire\CommentList;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Comment Model', function () {
    test('comment can be created', function () {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'author_name' => 'John Doe',
            'content' => 'This is a test comment',
        ]);

        expect($comment)->toBeInstanceOf(Comment::class);
        expect($comment->author_name)->toBe('John Doe');
        expect($comment->content)->toBe('This is a test comment');
    });

    test('comment belongs to post', function () {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        expect($comment->post->id)->toBe($post->id);
    });

    test('comment can have replies', function () {
        $comment = Comment::factory()->approved()->create();
        $reply = Comment::factory()->approved()->create([
            'post_id' => $comment->post_id,
            'parent_id' => $comment->id,
        ]);

        expect($comment->replies)->toHaveCount(1);
        expect($comment->replies->first()->id)->toBe($reply->id);
    });

    test('comment can be approved', function () {
        $post = Post::factory()->create(['comments_count' => 0]);
        $comment = Comment::factory()->pending()->create(['post_id' => $post->id]);

        expect($comment->status)->toBe(Comment::STATUS_PENDING);
        expect($comment->approved_at)->toBeNull();

        $comment->approve();
        $comment->refresh();
        $post->refresh();

        expect($comment->status)->toBe(Comment::STATUS_APPROVED);
        expect($comment->approved_at)->not->toBeNull();
        expect($post->comments_count)->toBe(1);
    });

    test('comment can be rejected', function () {
        $post = Post::factory()->create(['comments_count' => 1]);
        $comment = Comment::factory()->approved()->create(['post_id' => $post->id]);

        $comment->reject();
        $comment->refresh();
        $post->refresh();

        expect($comment->status)->toBe(Comment::STATUS_REJECTED);
        expect($comment->approved_at)->toBeNull();
        expect($post->comments_count)->toBe(0);
    });

    test('comment can be marked as spam', function () {
        $comment = Comment::factory()->pending()->create();

        $comment->markAsSpam();
        $comment->refresh();

        expect($comment->status)->toBe(Comment::STATUS_SPAM);
    });

    test('gravatar URL is generated correctly', function () {
        $comment = Comment::factory()->create(['author_email' => 'test@example.com']);

        $gravatarUrl = $comment->getGravatarUrl();

        expect($gravatarUrl)->toContain('gravatar.com/avatar/');
        expect($gravatarUrl)->toContain(md5('test@example.com'));
    });

    test('isReply returns correct status', function () {
        $rootComment = Comment::factory()->create(['parent_id' => null]);
        $reply = Comment::factory()->create([
            'post_id' => $rootComment->post_id,
            'parent_id' => $rootComment->id,
        ]);

        expect($rootComment->isReply())->toBeFalse();
        expect($reply->isReply())->toBeTrue();
    });

    test('getDepth returns correct depth', function () {
        $root = Comment::factory()->create();
        $level1 = Comment::factory()->create([
            'post_id' => $root->post_id,
            'parent_id' => $root->id,
        ]);
        $level2 = Comment::factory()->create([
            'post_id' => $root->post_id,
            'parent_id' => $level1->id,
        ]);

        expect($root->getDepth())->toBe(0);
        expect($level1->getDepth())->toBe(1);
        expect($level2->getDepth())->toBe(2);
    });
})->group('comments');

describe('Comment Scopes', function () {
    test('approved scope returns only approved comments', function () {
        Comment::factory()->approved()->count(3)->create();
        Comment::factory()->pending()->count(2)->create();

        expect(Comment::approved()->count())->toBe(3);
    });

    test('pending scope returns only pending comments', function () {
        Comment::factory()->approved()->count(3)->create();
        Comment::factory()->pending()->count(2)->create();

        expect(Comment::pending()->count())->toBe(2);
    });

    test('rootLevel scope returns only root comments', function () {
        $root = Comment::factory()->count(3)->create();
        Comment::factory()->create([
            'post_id' => $root->first()->post_id,
            'parent_id' => $root->first()->id,
        ]);

        expect(Comment::rootLevel()->count())->toBe(3);
    });
})->group('comments');

describe('CommentForm Livewire Component', function () {
    test('component renders for post with comments enabled', function () {
        $post = Post::factory()->create(['allow_comments' => true]);

        Livewire::test(CommentForm::class, ['post' => $post])
            ->assertStatus(200)
            ->assertSee(__('Name'))
            ->assertSee(__('Email'))
            ->assertSee(__('Comment'));
    });

    test('can submit comment', function () {
        $post = Post::factory()->create(['allow_comments' => true]);

        Livewire::test(CommentForm::class, ['post' => $post])
            ->set('authorName', 'Test User')
            ->set('authorEmail', 'test@example.com')
            ->set('content', 'This is a test comment.')
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'author_name' => 'Test User',
            'author_email' => 'test@example.com',
            'content' => 'This is a test comment.',
            'status' => Comment::STATUS_PENDING,
        ]);
    });

    test('validates required fields', function () {
        $post = Post::factory()->create(['allow_comments' => true]);

        Livewire::test(CommentForm::class, ['post' => $post])
            ->set('authorName', '')
            ->set('authorEmail', '')
            ->set('content', '')
            ->call('submit')
            ->assertHasErrors(['authorName', 'authorEmail', 'content']);
    });

    test('validates email format', function () {
        $post = Post::factory()->create(['allow_comments' => true]);

        Livewire::test(CommentForm::class, ['post' => $post])
            ->set('authorName', 'Test')
            ->set('authorEmail', 'not-an-email')
            ->set('content', 'Test content')
            ->call('submit')
            ->assertHasErrors(['authorEmail']);
    });

    test('auto-fills name and email for logged-in users', function () {
        $user = User::factory()->create([
            'name' => 'Auth User',
            'email' => 'auth@example.com',
        ]);
        $post = Post::factory()->create(['allow_comments' => true]);

        $this->actingAs($user);

        Livewire::test(CommentForm::class, ['post' => $post])
            ->assertSet('authorName', 'Auth User')
            ->assertSet('authorEmail', 'auth@example.com');
    });

    test('stores IP address and user agent', function () {
        $post = Post::factory()->create(['allow_comments' => true]);

        Livewire::test(CommentForm::class, ['post' => $post])
            ->set('authorName', 'Test User')
            ->set('authorEmail', 'test@example.com')
            ->set('content', 'Test comment with metadata')
            ->call('submit');

        $comment = Comment::where('author_email', 'test@example.com')->first();
        expect($comment->ip_address)->not->toBeNull();
    });

    test('sanitizes HTML from content', function () {
        $post = Post::factory()->create(['allow_comments' => true]);

        Livewire::test(CommentForm::class, ['post' => $post])
            ->set('authorName', 'Test User')
            ->set('authorEmail', 'test@example.com')
            ->set('content', '<script>alert("xss")</script>Hello')
            ->call('submit');

        $comment = Comment::where('author_email', 'test@example.com')->first();
        expect($comment->content)->not->toContain('<script>');
        expect($comment->content)->toContain('Hello');
    });
})->group('comments');

describe('Comment Spam Protection', function () {
    test('honeypot filled submissions are silently rejected', function () {
        $post = Post::factory()->create(['allow_comments' => true]);

        Livewire::test(CommentForm::class, ['post' => $post])
            ->set('authorName', 'Bot User')
            ->set('authorEmail', 'bot@example.com')
            ->set('content', 'Spam content')
            ->set('website', 'http://spam-site.com') // Honeypot filled
            ->call('submit')
            ->assertSet('submitted', true); // Appears successful

        // But comment should not be saved
        $this->assertDatabaseMissing('comments', [
            'author_email' => 'bot@example.com',
        ]);
    });

    test('rate limiting blocks excessive submissions', function () {
        $post = Post::factory()->create(['allow_comments' => true]);
        $limit = config('comments.rate_limit', 5);

        // Exhaust rate limit
        for ($i = 0; $i < $limit; $i++) {
            RateLimiter::hit('comment-submit:127.0.0.1', 60);
        }

        Livewire::test(CommentForm::class, ['post' => $post])
            ->set('authorName', 'Rate Limited User')
            ->set('authorEmail', 'rate@example.com')
            ->set('content', 'Test content')
            ->call('submit')
            ->assertHasErrors(['content']);

        $this->assertDatabaseMissing('comments', [
            'author_email' => 'rate@example.com',
        ]);
    });

    test('comments with links are held for moderation', function () {
        config(['comments.require_moderation' => false]);
        config(['comments.auto_hold_links' => true]);

        $post = Post::factory()->create(['allow_comments' => true]);

        Livewire::test(CommentForm::class, ['post' => $post])
            ->set('authorName', 'Link User')
            ->set('authorEmail', 'link@example.com')
            ->set('content', 'Check out https://example.com')
            ->call('submit');

        $comment = Comment::where('author_email', 'link@example.com')->first();
        expect($comment->status)->toBe(Comment::STATUS_PENDING);
    });
})->group('comments');

describe('CommentList Livewire Component', function () {
    test('component renders', function () {
        $post = Post::factory()->create(['allow_comments' => true]);

        Livewire::test(CommentList::class, ['post' => $post])
            ->assertStatus(200);
    });

    test('displays approved comments', function () {
        $post = Post::factory()->create(['allow_comments' => true]);
        $comment = Comment::factory()->approved()->create([
            'post_id' => $post->id,
            'author_name' => 'Visible User',
            'content' => 'Approved comment content',
        ]);

        Livewire::test(CommentList::class, ['post' => $post])
            ->assertSee('Visible User');
    });

    test('does not display pending comments', function () {
        $post = Post::factory()->create(['allow_comments' => true]);
        Comment::factory()->pending()->create([
            'post_id' => $post->id,
            'author_name' => 'Hidden User',
        ]);

        Livewire::test(CommentList::class, ['post' => $post])
            ->assertDontSee('Hidden User');
    });

    test('displays nested replies', function () {
        $post = Post::factory()->create(['allow_comments' => true]);
        $parent = Comment::factory()->approved()->create([
            'post_id' => $post->id,
            'author_name' => 'Parent User',
        ]);
        Comment::factory()->approved()->create([
            'post_id' => $post->id,
            'parent_id' => $parent->id,
            'author_name' => 'Reply User',
        ]);

        Livewire::test(CommentList::class, ['post' => $post])
            ->assertSee('Parent User')
            ->assertSee('Reply User');
    });

    test('can start reply', function () {
        $post = Post::factory()->create(['allow_comments' => true]);
        $comment = Comment::factory()->approved()->create(['post_id' => $post->id]);

        Livewire::test(CommentList::class, ['post' => $post])
            ->call('startReply', $comment->id)
            ->assertSet('replyingTo', $comment->id);
    });

    test('can cancel reply', function () {
        $post = Post::factory()->create(['allow_comments' => true]);
        $comment = Comment::factory()->approved()->create(['post_id' => $post->id]);

        Livewire::test(CommentList::class, ['post' => $post])
            ->call('startReply', $comment->id)
            ->assertSet('replyingTo', $comment->id)
            ->call('cancelReply')
            ->assertSet('replyingTo', null);
    });
})->group('comments');

describe('Post Comments Integration', function () {
    test('commentsAreEnabled returns true for enabled post', function () {
        $post = Post::factory()->create(['allow_comments' => true]);

        expect($post->commentsAreEnabled())->toBeTrue();
    });

    test('commentsAreEnabled returns false for disabled post', function () {
        $post = Post::factory()->create(['allow_comments' => false]);

        expect($post->commentsAreEnabled())->toBeFalse();
    });

    test('commentsAreEnabled respects global setting', function () {
        config(['comments.enabled' => false]);

        $post = Post::factory()->create(['allow_comments' => true]);

        expect($post->commentsAreEnabled())->toBeFalse();
    });

    test('commentsAreEnabled respects auto-close days', function () {
        config(['comments.auto_close_days' => 30]);

        $oldPost = Post::factory()->create([
            'allow_comments' => true,
            'published_at' => now()->subDays(60),
        ]);

        $newPost = Post::factory()->create([
            'allow_comments' => true,
            'published_at' => now()->subDays(10),
        ]);

        expect($oldPost->commentsAreEnabled())->toBeFalse();
        expect($newPost->commentsAreEnabled())->toBeTrue();
    });

    test('approvedComments returns only root approved comments', function () {
        $post = Post::factory()->create();
        Comment::factory()->approved()->count(3)->create(['post_id' => $post->id]);
        Comment::factory()->pending()->count(2)->create(['post_id' => $post->id]);

        $approved = Comment::factory()->approved()->create(['post_id' => $post->id]);
        Comment::factory()->approved()->create([
            'post_id' => $post->id,
            'parent_id' => $approved->id,
        ]);

        expect($post->approvedComments()->count())->toBe(4);
    });
})->group('comments');

describe('Comment Factory States', function () {
    test('approved state sets correct status', function () {
        $comment = Comment::factory()->approved()->create();

        expect($comment->status)->toBe(Comment::STATUS_APPROVED);
        expect($comment->approved_at)->not->toBeNull();
    });

    test('pending state sets correct status', function () {
        $comment = Comment::factory()->pending()->create();

        expect($comment->status)->toBe(Comment::STATUS_PENDING);
        expect($comment->approved_at)->toBeNull();
    });

    test('rejected state sets correct status', function () {
        $comment = Comment::factory()->rejected()->create();

        expect($comment->status)->toBe(Comment::STATUS_REJECTED);
    });

    test('spam state sets correct status', function () {
        $comment = Comment::factory()->spam()->create();

        expect($comment->status)->toBe(Comment::STATUS_SPAM);
    });

    test('replyTo state sets parent correctly', function () {
        $parent = Comment::factory()->create();
        $reply = Comment::factory()->replyTo($parent)->create();

        expect($reply->parent_id)->toBe($parent->id);
        expect($reply->post_id)->toBe($parent->post_id);
    });
})->group('comments');
