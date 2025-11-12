<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated using our custom session data
        if (!session('authenticated') || !session('user_id') || !session('user_type')) {
            // If this is an API/JSON request, return JSON response
            if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in to access this resource.',
                    'error' => 'Unauthenticated'
                ], 401);
            }
            // Otherwise redirect to login
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Make user data available to views
        $request->attributes->add([
            'user_id' => session('user_id'),
            'user_type' => session('user_type'),
            'user_email' => session('user_email'),
            'user_name' => session('user_name'),
        ]);

        return $next($request);
    }
}
