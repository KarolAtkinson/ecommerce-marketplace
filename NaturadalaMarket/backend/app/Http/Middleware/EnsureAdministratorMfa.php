<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdministratorMfa
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole('admin'), 403);
        abort_unless($user->two_factor_confirmed_at !== null, 403, 'Skonfiguruj uwierzytelnianie dwuetapowe przed otwarciem panelu administracyjnego.');

        return $next($request);
    }
}
