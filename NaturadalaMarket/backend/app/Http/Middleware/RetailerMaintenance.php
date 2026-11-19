<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RetailerMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('features.retailer_maintenance') && ! $request->user()?->hasRole('admin')) {
            return response()->view('retailer.maintenance', [], 503, ['Retry-After' => '86400']);
        }

        return $next($request);
    }
}
