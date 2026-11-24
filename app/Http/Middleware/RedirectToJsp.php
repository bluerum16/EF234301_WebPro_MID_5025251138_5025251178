<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToJsp
{
    public function handle(Request $request, Closure $next): Response
    {
        $frontend = config('app.jsp_url');

        if ($frontend && $request->isMethodSafe() && ! $request->expectsJson()) {
            return redirect()->away(rtrim($frontend, '/').'/app/dashboard');
        }

        return $next($request);
    }
}
