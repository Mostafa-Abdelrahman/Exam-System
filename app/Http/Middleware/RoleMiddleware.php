<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user() || !$request->user()->hasRole($role)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}

// This middleware checks if the authenticated user has the specified role.
// If the user does not have the role, a 403 Unauthorized response is returned.
// If the user has the role, the request is passed to the next middleware or controller.
// To use this middleware, you can register it in the app/Http/Kernel.php file and apply it to your routes or controllers.
// Example of registering the middleware in app/Http/Kernel.php
// protected $routeMiddleware = [
//     // ...
//     'role' => \App\Http\Middleware\RoleMiddleware::class,
// ];
// Example of applying the middleware to a route in routes/web.php
// Route::get('/admin', function () {
//     // Admin dashboard
// })->middleware('role:admin');
// Example of applying the middleware to a controller method
// public function __construct()            