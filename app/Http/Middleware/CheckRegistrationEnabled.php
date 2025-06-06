<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistrationEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle($request, Closure $next)
    {
        if (!config('settings.register.enabled') && $request->routeIs('register')) {
            abort(403, 'Registration is currently disabled');
        }

        return $next($request);
    }
}
