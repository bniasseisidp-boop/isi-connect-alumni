<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrackLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $request->user()->updateQuietly(['last_seen_at' => now()]);
        }
        return $next($request);
    }
}
