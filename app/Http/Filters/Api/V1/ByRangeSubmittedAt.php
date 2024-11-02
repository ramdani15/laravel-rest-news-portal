<?php

namespace App\Http\Filters\Api\V1;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ByRangeSubmittedAt
{
    public function __construct(protected Request $request)
    {
        //
    }

    public function handle(Builder $builder, \Closure $next)
    {
        return $next($builder)
            ->when($this->request->has('start_submitted') && $this->request->has('end_submitted'), function ($query) {
                $query->where('submitted_at', '>=', $this->request->start_submitted.' 00:00:00')
                    ->where('submitted_at', '<=', $this->request->end_submitted.' 23:59:59');
            });
    }
}
