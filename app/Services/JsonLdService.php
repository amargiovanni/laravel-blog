<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Support\Str;

class JsonLdService
{
    private const MAX_HEADLINE_LENGTH = 110;

    /**
     * Generate JSON-LD for a blog post.
     *
     * @return array<string, mixed>
     */
    public function forPost(Post $post): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => Str::limit($post->title, self::MAX_HEADLINE_LENGTH, ''),
            'description' => $post->excerpt,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author->name,
                'url' => url('/'),
            ],
            'publisher' => $this->getPublisher(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => url("/posts/{$post->slug}"),
            ],
        ];

        // Add featured image if available
        if ($post->featuredImage) {
            $data['image'] = url('storage/'.$post->featuredImage->path);
        }

        // Add article section from first category
        $post->loadMissing('categories');
        if ($post->categories->isNotEmpty()) {
            $data['articleSection'] = $post->categories->first()->name;
        }

        return $data;
    }

    /**
     * Generate JSON-LD for the organization.
     *
     * @return array<string, mixed>
     */
    public function forOrganization(): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('blog.name', config('app.name')),
            'url' => config('app.url'),
        ];

        $logo = Setting::get('theme.logo');
        if ($logo) {
            $data['logo'] = [
                '@type' => 'ImageObject',
                'url' => url('storage/'.$logo),
            ];
        }

        return $data;
    }

    /**
     * Generate JSON-LD for the website.
     *
     * @return array<string, mixed>
     */
    public function forWebsite(): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('blog.name', config('app.name')),
            'url' => config('app.url'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => url('/search?q={search_term_string}'),
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * Convert JSON-LD array to script tag.
     *
     * @param  array<string, mixed>  $data
     */
    public function toScript(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        return '<script type="application/ld+json">'.json_encode($data, JSON_UNESCAPED_SLASHES).'</script>';
    }

    /**
     * Check if JSON-LD generation is enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) Setting::get('geo.jsonld_enabled', true);
    }

    /**
     * Get the publisher data.
     *
     * @return array<string, mixed>
     */
    private function getPublisher(): array
    {
        $publisher = [
            '@type' => 'Organization',
            'name' => config('blog.name', config('app.name')),
        ];

        $logo = Setting::get('theme.logo');
        if ($logo) {
            $publisher['logo'] = [
                '@type' => 'ImageObject',
                'url' => url('storage/'.$logo),
            ];
        }

        return $publisher;
    }
}
