<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// -- begin --
use App;
// -- einde --

class TaalManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // -- begin --
        if ($request->session()->has('taal')) {
            App::setLocale($request->session()->get('taal'));
        }
        // -- einde --

        return $next($request);
    }
}
