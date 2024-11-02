<?php

namespace App\Http\Filters\Api\V1;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ByParentId
{
    public function __construct(protected Request $request)
    {
        //
    }

    public function handle(Builder $builder, \Closure $next)
    {
        return $next($builder)
            ->when($this->request->has('parent_id'), function ($query) {
                $query->where('parent_id', $this->request->parent_id);
            });
    }
}
