<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\LogsActivityAllDirty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriberFactory> */
    use HasFactory;

    use LogsActivityAllDirty;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'name',
        'unsubscribe_token',
        'verified_at',
        'unsubscribed_at',
        'subscribed_ip',
    ];

    /**
     * Generate a unique unsubscribe token.
     */
    public static function generateUnsubscribeToken(): string
    {
        do {
            $token = Str::random(64);
        } while (static::where('unsubscribe_token', $token)->exists());

        return $token;
    }

    /**
     * Scope for verified subscribers.
     *
     * @param  Builder<Subscriber>  $query
     * @return Builder<Subscriber>
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope for active (verified and not unsubscribed) subscribers.
     *
     * @param  Builder<Subscriber>  $query
     * @return Builder<Subscriber>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('verified_at')
            ->whereNull('unsubscribed_at');
    }

    /**
     * Scope for unsubscribed subscribers.
     *
     * @param  Builder<Subscriber>  $query
     * @return Builder<Subscriber>
     */
    public function scopeUnsubscribed(Builder $query): Builder
    {
        return $query->whereNotNull('unsubscribed_at');
    }

    /**
     * Scope for unverified subscribers.
     *
     * @param  Builder<Subscriber>  $query
     * @return Builder<Subscriber>
     */
    public function scopeUnverified(Builder $query): Builder
    {
        return $query->whereNull('verified_at');
    }

    /**
     * Check if subscriber is verified.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Check if subscriber is active (verified and not unsubscribed).
     */
    public function isActive(): bool
    {
        return $this->isVerified() && $this->unsubscribed_at === null;
    }

    /**
     * Mark subscriber as verified.
     */
    public function markAsVerified(): self
    {
        $this->update(['verified_at' => now()]);

        return $this;
    }

    /**
     * Mark subscriber as unsubscribed.
     */
    public function markAsUnsubscribed(): self
    {
        $this->update(['unsubscribed_at' => now()]);

        return $this;
    }

    /**
     * Generate the unsubscribe URL for this subscriber.
     */
    public function getUnsubscribeUrl(): string
    {
        return route('newsletter.unsubscribe', $this->unsubscribe_token);
    }

    protected static function booted(): void
    {
        static::creating(function (Subscriber $subscriber): void {
            if (empty($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = static::generateUnsubscribeToken();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }
}
