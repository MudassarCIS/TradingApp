<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Only allow staff members (admin, manager, moderator) to access admin panel
        if (!$user || !$user->hasAnyRole(['admin', 'manager', 'moderator'])) {
            return redirect()->route('customer.dashboard')->with('error', 'Access denied. You do not have permission to access the admin panel.');
        }
        
        return $next($request);
    }
}
