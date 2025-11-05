<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class HandleTokenExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // If not authenticated and this is not a guest route, redirect to home with error
            if (!$request->routeIs('login', 'register', 'dashboard', 'home', 'password.*', 'auth.*')) {
                return redirect()->route('home')->with('error', 'Your session has expired. Please login again.');
            }
        }

        return $next($request);
    }
}

