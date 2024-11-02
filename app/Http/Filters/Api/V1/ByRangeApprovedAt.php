<?php

namespace App\Http\Filters\Api\V1;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ByRangeApprovedAt
{
    public function __construct(protected Request $request)
    {
        //
    }

    public function handle(Builder $builder, \Closure $next)
    {
        return $next($builder)
            ->when($this->request->has('start_approved') && $this->request->has('end_approved'), function ($query) {
                $query->where('approved_at', '>=', $this->request->start_approved.' 00:00:00')
                    ->where('approved_at', '<=', $this->request->end_approved.' 23:59:59');
            });
    }
}
