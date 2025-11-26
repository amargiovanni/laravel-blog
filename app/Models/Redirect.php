<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\LogsActivityAllDirty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redirect extends Model
{
    /** @use HasFactory<\Database\Factories\RedirectFactory> */
    use HasFactory;

    use LogsActivityAllDirty;

    public const STATUS_PERMANENT = 301;

    public const STATUS_TEMPORARY = 302;

    protected static string $cacheKey = 'redirects:all_active';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'source_url',
        'target_url',
        'status_code',
        'is_active',
        'is_automatic',
        'hits',
        'last_hit_at',
    ];

    /**
     * Find a redirect by source URL.
     */
    public static function findBySourceUrl(string $url): ?self
    {
        $normalizedUrl = '/'.ltrim(rtrim($url, '/'), '/');

        return static::active()->where('source_url', $normalizedUrl)->first();
    }

    /**
     * Get cached active redirects.
     *
     * @return array<string, array{target_url: string, status_code: int, id: int}>
     */
    public static function getCachedRedirects(): array
    {
        return Cache::remember(static::$cacheKey, 3600, function () {
            $redirects = static::active()->get();

            $result = [];
            foreach ($redirects as $redirect) {
                $result[$redirect->source_url] = [
                    'target_url' => $redirect->target_url,
                    'status_code' => $redirect->status_code,
                    'id' => $redirect->id,
                ];
            }

            return $result;
        });
    }

    /**
     * Clear the redirect cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(static::$cacheKey);
    }

    /**
     * Scope for active redirects.
     *
     * @param  Builder<Redirect>  $query
     * @return Builder<Redirect>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for automatic redirects.
     *
     * @param  Builder<Redirect>  $query
     * @return Builder<Redirect>
     */
    public function scopeAutomatic(Builder $query): Builder
    {
        return $query->where('is_automatic', true);
    }

    /**
     * Scope for manual redirects.
     *
     * @param  Builder<Redirect>  $query
     * @return Builder<Redirect>
     */
    public function scopeManual(Builder $query): Builder
    {
        return $query->where('is_automatic', false);
    }

    /**
     * Record a hit for this redirect.
     */
    public function recordHit(): void
    {
        $this->increment('hits');
        $this->update(['last_hit_at' => now()]);
    }

    /**
     * Check if this redirect would create a loop.
     */
    public function wouldCreateLoop(): bool
    {
        // Direct self-redirect
        if ($this->normalizeUrl($this->source_url) === $this->normalizeUrl($this->target_url)) {
            return true;
        }

        // Check for indirect loops (A→B→A)
        return $this->detectLoopFromTarget($this->target_url, [$this->normalizeUrl($this->source_url)]);
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    /**
     * Detect if following redirects from a target would create a loop.
     *
     * @param  array<string>  $visited
     */
    protected function detectLoopFromTarget(string $url, array $visited): bool
    {
        $normalizedUrl = $this->normalizeUrl($url);

        // Check if we've already visited this URL
        if (in_array($normalizedUrl, $visited, true)) {
            return true;
        }

        // Find redirect for this URL
        $query = static::query()->active()->where('source_url', $normalizedUrl);

        // Exclude current redirect from the check
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        $nextRedirect = $query->first();

        if (! $nextRedirect) {
            return false;
        }

        // Continue following the chain
        $visited[] = $normalizedUrl;

        return $this->detectLoopFromTarget($nextRedirect->target_url, $visited);
    }

    /**
     * Normalize a URL for comparison.
     */
    protected function normalizeUrl(string $url): string
    {
        // Remove trailing slashes and ensure leading slash
        $url = '/'.ltrim(rtrim($url, '/'), '/');

        return strtolower($url);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'is_active' => 'boolean',
            'is_automatic' => 'boolean',
            'hits' => 'integer',
            'last_hit_at' => 'datetime',
        ];
    }
}
