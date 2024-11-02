<?php

namespace App\Models;

use App\Enums\ReactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reaction extends Model
{
    use HasFactory;
    use MainModel;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'reactionable_type',
        'reactionable_id',
        'type',
    ];

    public function casts(): array
    {
        return [
            'type' => ReactionType::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function reactionable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeLike($q)
    {
        return $q->where('type', ReactionType::LIKE->value);
    }

    public function scopeDislike($q)
    {
        return $q->where('type', ReactionType::DISLIKE->value);
    }
}
