<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // CORS middleware for API routes
        $middleware->api(prepend: [
            \Fruitcake\Cors\HandleCors::class,
        ]);
        
        // spatti rolls and permissions
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'admin.access' => \App\Http\Middleware\AdminAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle authentication exceptions - redirect to home with error message
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Token expired. Please login again.'], 401);
            }
            
            return redirect()->route('home')->with('error', 'Your session has expired. Please login again.');
        });
        
        // Handle Spatie Permission unauthorized exceptions
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Access denied. You do not have the required role to access this resource.',
                    'message' => $e->getMessage()
                ], 403);
            }
            
            // For web requests, redirect to appropriate dashboard based on user role
            $user = auth()->user();
            if ($user && $user->isCustomer()) {
                return redirect()->route('customer.dashboard')->with('error', 'Access denied. You do not have permission to access this resource.');
            }
            
            return redirect()->route('home')->with('error', 'Access denied. You do not have permission to access this resource.');
        });
    })->create();
