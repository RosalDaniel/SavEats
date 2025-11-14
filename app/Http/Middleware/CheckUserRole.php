<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  The allowed user roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userType = session('user_type');
        
        // Check if user is authenticated
        if (!session('authenticated') || !$userType) {
            if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in to access this resource.',
                    'error' => 'Unauthenticated'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }
        
        // Check if user has one of the allowed roles
        if (!in_array($userType, $roles)) {
            if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to access this resource.',
                    'error' => 'Forbidden'
                ], 403);
            }
            
            // Redirect to appropriate dashboard based on user type
            $dashboardRoute = match($userType) {
                'consumer' => 'dashboard.consumer',
                'establishment' => 'establishment.dashboard',
                'foodbank' => 'foodbank.dashboard',
                'admin' => 'dashboard.admin',
                default => 'login'
            };
            
            return redirect()->route($dashboardRoute)->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
