<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\LogsActivityAllDirty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Revision extends Model
{
    /** @use HasFactory<\Database\Factories\RevisionFactory> */
    use HasFactory, LogsActivityAllDirty;

    public $timestamps = false;

    protected $fillable = [
        'revisionable_type',
        'revisionable_id',
        'user_id',
        'revision_number',
        'title',
        'content',
        'excerpt',
        'metadata',
        'is_autosave',
        'is_protected',
        'created_at',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAuthorName(): string
    {
        return $this->user?->name ?? 'Unknown';
    }

    public function isAutosave(): bool
    {
        return $this->is_autosave;
    }

    public function isProtected(): bool
    {
        return $this->is_protected;
    }

    public function getMetadata(string $key, mixed $default = null): mixed
    {
        return data_get($this->metadata, $key, $default);
    }

    protected static function booted(): void
    {
        static::creating(function (Revision $revision): void {
            $revision->created_at ??= now();
        });
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_autosave' => 'boolean',
            'is_protected' => 'boolean',
            'created_at' => 'datetime',
            'revision_number' => 'integer',
        ];
    }
}
