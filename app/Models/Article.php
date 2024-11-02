<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory;
    use MainModel;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'status',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'published_at',
    ];

    public function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function parentComments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactionable');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactionable')->like();
    }

    public function dislikes(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactionable')->dislike();
    }

    public function getTotalCommentsAttribute(): int
    {
        return $this->parentComments()->count();
    }

    public function getTotalLikesAttribute(): int
    {
        return $this->likes()->count();
    }

    public function getTotalDislikesAttribute(): int
    {
        return $this->dislikes()->count();
    }

    public function getIsLikedAttribute(): bool
    {
        if (! auth()->user()) {
            return false;
        }

        return $this->likes()->where('user_id', auth()->id())->exists();
    }

    public function getIsDislikedAttribute(): bool
    {
        if (! auth()->user()) {
            return false;
        }

        return $this->dislikes()->where('user_id', auth()->id())->exists();
    }
}
