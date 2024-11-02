<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use MainModel;

    protected $fillable = [
        'logable_type',
        'logable_id',
        'user_id',
        'log_type',
        'log_data',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Relation to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation to logable models.
     */
    public function logable()
    {
        return $this->morphTo();
    }
}
