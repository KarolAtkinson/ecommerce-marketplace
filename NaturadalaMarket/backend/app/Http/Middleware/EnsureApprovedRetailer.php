<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedRetailer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user?->isApprovedRetailer(), 403, 'Dostęp do przestrzeni partnera wymaga zatwierdzonego konta firmy.');

        return $next($request);
    }
}
