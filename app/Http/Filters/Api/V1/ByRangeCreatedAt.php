<?php

namespace App\Http\Filters\Api\V1;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ByRangeCreatedAt
{
    public function __construct(protected Request $request)
    {
        //
    }

    public function handle(Builder $builder, \Closure $next)
    {
        return $next($builder)
            ->when($this->request->has('start_created') && $this->request->has('end_created'), function ($query) {
                $query->where('created_at', '>=', $this->request->start_created.' 00:00:00')
                    ->where('created_at', '<=', $this->request->end_created.' 23:59:59');
            });
    }
}
