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

        // Check if user account is suspended (for non-admin users)
        $userType = session('user_type');
        $userId = session('user_id');
        
        if ($userType !== 'admin' && $userId) {
            $user = null;
            
            switch ($userType) {
                case 'consumer':
                    $user = \App\Models\Consumer::find($userId);
                    break;
                case 'establishment':
                    $user = \App\Models\Establishment::find($userId);
                    break;
                case 'foodbank':
                    $user = \App\Models\Foodbank::find($userId);
                    break;
            }
            
            // Get latest status directly from database to ensure we have the most current value
            if ($user) {
                $modelClass = match($userType) {
                    'consumer' => \App\Models\Consumer::class,
                    'establishment' => \App\Models\Establishment::class,
                    'foodbank' => \App\Models\Foodbank::class,
                    default => null
                };
                
                if ($modelClass) {
                    $latestStatus = $modelClass::where($user->getKeyName(), $userId)->value('status');
                    $status = strtolower(trim($latestStatus ?? ''));
                    
                    // If user is suspended, logout and redirect to login with message
                    if ($status === 'suspended') {
                        // Clear session
                        $request->session()->forget(['user_id', 'user_type', 'user_email', 'user_name', 'user_profile_picture', 'fname', 'lname', 'authenticated']);
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
                        
                        // If this is an API/JSON request, return JSON response
                        if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Your account has been suspended. For assistance, please contact our support team at saveats.helpdesk@gmail.com or call our support number.',
                                'error' => 'Account Suspended'
                            ], 403);
                        }
                        
                        // Redirect to login with suspension message
                        return redirect()->route('login')->with('error', 'Your account has been suspended. For assistance, please contact our support team at saveats.helpdesk@gmail.com or call our support number.');
                    }
                }
            }
        }

        // Make user data available to views
        $request->attributes->add([
            'user_id' => session('user_id'),
            'user_type' => session('user_type'),
            'user_email' => session('user_email'),
            'user_name' => session('user_name'),
        ]);

        // Get the response
        $response = $next($request);

        // Add headers to prevent browser caching of authenticated pages
        // This ensures the browser always checks with the server instead of serving cached pages
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
