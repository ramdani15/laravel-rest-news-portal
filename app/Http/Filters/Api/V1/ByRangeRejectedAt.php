<?php

namespace App\Http\Filters\Api\V1;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ByRangeRejectedAt
{
    public function __construct(protected Request $request)
    {
        //
    }

    public function handle(Builder $builder, \Closure $next)
    {
        return $next($builder)
            ->when($this->request->has('start_rejected') && $this->request->has('end_rejected'), function ($query) {
                $query->where('rejected_at', '>=', $this->request->start_rejected.' 00:00:00')
                    ->where('rejected_at', '<=', $this->request->end_rejected.' 23:59:59');
            });
    }
}
