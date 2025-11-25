<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;

class LlmsTxtService
{
    private const DEFAULT_POST_LIMIT = 20;

    /**
     * Generate the llms.txt content.
     */
    public function generate(): string
    {
        if (! $this->isEnabled()) {
            return '';
        }

        $lines = [];

        // Header - Site name as H1 (required)
        $lines[] = '# '.config('blog.name', config('app.name'));
        $lines[] = '';

        // Description as blockquote (optional)
        $description = config('blog.description');
        if ($description) {
            $lines[] = '> '.$description;
            $lines[] = '';
        }

        // Blog Posts section
        if ($this->shouldIncludePosts()) {
            $postsSection = $this->generatePostsSection();
            if (! empty($postsSection)) {
                $lines = array_merge($lines, $postsSection);
            }
        }

        // Categories section
        $categoriesSection = $this->generateCategoriesSection();
        if (! empty($categoriesSection)) {
            $lines = array_merge($lines, $categoriesSection);
        }

        return implode("\n", $lines);
    }

    /**
     * Validate the generated llms.txt content.
     *
     * @return array{valid: bool, errors: array<string>}
     */
    public function validate(): array
    {
        $content = $this->generate();
        $errors = [];

        // Must have content if enabled
        if ($this->isEnabled() && empty(trim($content))) {
            $errors[] = 'llms.txt content is empty';
        }

        // If disabled, empty is valid
        if (! $this->isEnabled()) {
            return [
                'valid' => false,
                'errors' => ['llms.txt generation is disabled'],
            ];
        }

        // Must start with H1
        if (! preg_match('/^# .+$/m', $content)) {
            $errors[] = 'llms.txt must start with an H1 header (# Site Name)';
        }

        // Check for valid markdown links
        if (preg_match_all('/\[([^\]]*)\]\(([^)]*)\)/', $content, $matches)) {
            foreach ($matches[2] as $url) {
                if (! filter_var($url, FILTER_VALIDATE_URL)) {
                    $errors[] = "Invalid URL found: {$url}";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Check if llms.txt generation is enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) Setting::get('geo.llms_enabled', true);
    }

    /**
     * Check if posts should be included.
     */
    private function shouldIncludePosts(): bool
    {
        return (bool) Setting::get('geo.llms_include_posts', true);
    }

    /**
     * Generate the posts section.
     *
     * @return array<string>
     */
    private function generatePostsSection(): array
    {
        $posts = Post::query()
            ->published()
            ->orderBy('published_at', 'desc')
            ->limit(self::DEFAULT_POST_LIMIT)
            ->get();

        if ($posts->isEmpty()) {
            return [];
        }

        $lines = [];
        $lines[] = '## Blog Posts';
        $lines[] = '';

        foreach ($posts as $post) {
            $url = url("/posts/{$post->slug}");
            $lines[] = "- [{$post->title}]({$url})";
        }

        $lines[] = '';

        return $lines;
    }

    /**
     * Generate the categories section.
     *
     * @return array<string>
     */
    private function generateCategoriesSection(): array
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        if ($categories->isEmpty()) {
            return [];
        }

        $lines = [];
        $lines[] = '## Categories';
        $lines[] = '';

        foreach ($categories as $category) {
            $url = url("/categories/{$category->slug}");
            $lines[] = "- [{$category->name}]({$url})";
        }

        $lines[] = '';

        return $lines;
    }
}
