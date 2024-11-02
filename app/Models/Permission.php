<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Shanmuga\LaravelEntrust\Models\EntrustPermission;

class Permission extends EntrustPermission
{
    use HasFactory;

    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Get the user's roles.
     */
    public function getRoleArrayAttribute(): array
    {
        $arr = [];
        if (! $this->roles) {
            return $arr;
        }
        foreach ($this->roles as $role) {
            $arr[$role->id] = $role->name;
        }

        return $arr;
    }
}
