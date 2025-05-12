<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     * 
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle(Request $request, Closure $next, ...$roles): mixed
    {
        if (!$request->user()) {
            throw new AuthenticationException('Unauthenticated.');
        }

        if (empty($roles)) {
            return $next($request);
        }

        foreach ($roles as $role) {
            // Check if user has the role
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        // If we reach here, the user doesn't have any of the required roles
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to access this resource.',
        ], Response::HTTP_FORBIDDEN);
    }
}