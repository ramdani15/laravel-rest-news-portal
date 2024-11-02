<?php

namespace App\Http\Filters\Api\V1;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ByRangePublishedAt
{
    public function __construct(protected Request $request)
    {
        //
    }

    public function handle(Builder $builder, \Closure $next)
    {
        return $next($builder)
            ->when($this->request->has('start_published') && $this->request->has('end_published'), function ($query) {
                $query->where('published_at', '>=', $this->request->start_published.' 00:00:00')
                    ->where('published_at', '<=', $this->request->end_published.' 23:59:59');
            });
    }
}
