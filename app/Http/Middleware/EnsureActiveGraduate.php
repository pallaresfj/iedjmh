<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveGraduate
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $graduate = $request->user('graduate');

        if ($graduate !== null && ! $graduate->isActive()) {
            Auth::guard('graduate')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('egresados.index')
                ->with('egresados_error', 'Tu cuenta de egresado no se encuentra activa en este momento.');
        }

        return $next($request);
    }
}
