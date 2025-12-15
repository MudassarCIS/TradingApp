<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get CORS configuration
        $corsPaths = config('cors.paths', ['api/*']);
        $allowedOrigins = config('cors.allowed_origins', ['*']);
        $allowedMethods = config('cors.allowed_methods', ['*']);
        $allowedHeaders = config('cors.allowed_headers', ['*']);
        $supportsCredentials = config('cors.supports_credentials', true);
        $maxAge = config('cors.max_age', 0);
        
        // Check if the current path matches any CORS path pattern
        $pathMatches = false;
        $requestPath = $request->path();
        
        foreach ($corsPaths as $path) {
            // Convert wildcard pattern to regex
            $pattern = str_replace(['*', '/'], ['.*', '\/'], $path);
            if (preg_match('/^' . $pattern . '$/', $requestPath)) {
                $pathMatches = true;
                break;
            }
        }
        
        // If path doesn't match CORS paths, continue without CORS headers
        if (!$pathMatches && !$request->is('api/*')) {
            return $next($request);
        }
        
        // Get origin from request
        $origin = $request->headers->get('Origin');
        
        // Handle preflight requests
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }
        
        // Check if origin is allowed
        $isAllowedOrigin = false;
        if (in_array('*', $allowedOrigins)) {
            $isAllowedOrigin = true;
        } elseif ($origin && in_array($origin, $allowedOrigins)) {
            $isAllowedOrigin = true;
        }
        
        // Set CORS headers
        if ($isAllowedOrigin && $origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } elseif (in_array('*', $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }
        
        // Set allowed methods
        if (in_array('*', $allowedMethods)) {
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        } else {
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', $allowedMethods));
        }
        
        // Set allowed headers
        if (in_array('*', $allowedHeaders)) {
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, Accept, Origin');
        } else {
            $response->headers->set('Access-Control-Allow-Headers', implode(', ', $allowedHeaders));
        }
        
        // Set credentials support
        if ($supportsCredentials) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
        
        // Set max age
        if ($maxAge > 0) {
            $response->headers->set('Access-Control-Max-Age', (string) $maxAge);
        }
        
        // Set exposed headers
        $exposedHeaders = config('cors.exposed_headers', []);
        if (!empty($exposedHeaders)) {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $exposedHeaders));
        }
        
        return $response;
    }
}
