<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
{
    if (auth()->check() && auth()->user()->hasRole('admin')) {
        return $next($request);
    }

    return redirect()->route('dashboard')->with('error', 'У вас недостаточно прав для доступа к этой странице');
}
}
