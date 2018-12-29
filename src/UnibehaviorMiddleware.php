<?php

namespace Reallyli\LaravelUnibehavior;

use Closure;

class UnibehaviorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        app('unibehavior')->record();

        return $next($request);
    }
}