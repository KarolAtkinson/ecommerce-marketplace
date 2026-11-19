<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', 'pl');
        app()->setLocale(in_array($locale, ['pl', 'en'], true) ? $locale : 'pl');

        return $next($request);
    }
}
