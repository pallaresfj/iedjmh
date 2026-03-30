<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateGraduate
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('graduate')->check()) {
            return redirect()->route('egresados.index');
        }

        Auth::shouldUse('graduate');

        return $next($request);
    }
}

