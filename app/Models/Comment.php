<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory;
    use MainModel;
    use SoftDeletes;

    protected $fillable = [
        'article_id',
        'user_id',
        'parent_id',
        'content',
    ];

    public function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
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

    public function getTotalRepliesAttribute(): int
    {
        return $this->replies()->count();
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

    public function scopeParent($q)
    {
        return $q->whereNull('parent_id');
    }
}
