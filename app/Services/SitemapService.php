<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    /**
     * Generate the sitemap with all content.
     */
    public function generate(): Sitemap
    {
        $sitemap = Sitemap::create();

        $this->addHomepage($sitemap);
        $this->addBlogIndex($sitemap);
        $this->addPosts($sitemap);
        $this->addPages($sitemap);
        $this->addCategories($sitemap);
        $this->addTags($sitemap);
        $this->addArchives($sitemap);

        return $sitemap;
    }

    /**
     * Write sitemap to a file.
     */
    public function writeToFile(string $path): void
    {
        $this->generate()->writeToFile($path);
    }

    /**
     * Get the XML string representation of the sitemap.
     */
    public function toXml(): string
    {
        return $this->generate()->render();
    }

    /**
     * Add homepage to sitemap.
     */
    protected function addHomepage(Sitemap $sitemap): void
    {
        $sitemap->add(
            Url::create('/')
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );
    }

    /**
     * Add blog index page to sitemap.
     */
    protected function addBlogIndex(Sitemap $sitemap): void
    {
        $sitemap->add(
            Url::create(route('posts.index'))
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );
    }

    /**
     * Add published posts to sitemap.
     */
    protected function addPosts(Sitemap $sitemap): void
    {
        Post::published()
            ->with('featuredImage')
            ->orderBy('published_at', 'desc')
            ->get()
            ->each(function (Post $post) use ($sitemap) {
                $url = Url::create(route('posts.show', $post->slug))
                    ->setLastModificationDate($post->updated_at)
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);

                // Add featured image if available
                if ($post->featuredImage) {
                    $url->addImage($post->featuredImage->url, $post->title);
                }

                $sitemap->add($url);
            });
    }

    /**
     * Add published pages to sitemap.
     */
    protected function addPages(Sitemap $sitemap): void
    {
        Page::published()
            ->with('featuredImage')
            ->orderBy('title')
            ->get()
            ->each(function (Page $page) use ($sitemap) {
                $url = Url::create(route('pages.show', $page->slug))
                    ->setLastModificationDate($page->updated_at)
                    ->setPriority(0.7)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY);

                // Add featured image if available
                if ($page->featuredImage) {
                    $url->addImage($page->featuredImage->url, $page->title);
                }

                $sitemap->add($url);
            });
    }

    /**
     * Add categories with posts to sitemap.
     */
    protected function addCategories(Sitemap $sitemap): void
    {
        // Add categories index
        $sitemap->add(
            Url::create(route('categories.index'))
                ->setPriority(0.6)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
        );

        // Add individual category pages
        Category::withCount(['posts' => fn ($q) => $q->published()])
            ->get()
            ->filter(fn ($cat) => $cat->posts_count > 0)
            ->each(function (Category $category) use ($sitemap) {
                // Get latest post date for this category
                $latestPost = $category->posts()
                    ->published()
                    ->latest('updated_at')
                    ->first();

                $url = Url::create(route('categories.show', $category->slug))
                    ->setPriority(0.6)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);

                if ($latestPost) {
                    $url->setLastModificationDate($latestPost->updated_at);
                }

                $sitemap->add($url);
            });
    }

    /**
     * Add tags with posts to sitemap.
     */
    protected function addTags(Sitemap $sitemap): void
    {
        // Add tags index
        $sitemap->add(
            Url::create(route('tags.index'))
                ->setPriority(0.5)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
        );

        // Add individual tag pages
        Tag::withCount(['posts' => fn ($q) => $q->published()])
            ->get()
            ->filter(fn ($tag) => $tag->posts_count > 0)
            ->each(function (Tag $tag) use ($sitemap) {
                // Get latest post date for this tag
                $latestPost = $tag->posts()
                    ->published()
                    ->latest('updated_at')
                    ->first();

                $url = Url::create(route('tags.show', $tag->slug))
                    ->setPriority(0.5)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);

                if ($latestPost) {
                    $url->setLastModificationDate($latestPost->updated_at);
                }

                $sitemap->add($url);
            });
    }

    /**
     * Add archives page to sitemap.
     */
    protected function addArchives(Sitemap $sitemap): void
    {
        $sitemap->add(
            Url::create(route('archives'))
                ->setPriority(0.5)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
        );
    }
}
