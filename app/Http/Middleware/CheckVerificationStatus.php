<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Establishment;
use App\Models\Foodbank;
use Symfony\Component\HttpFoundation\Response;

class CheckVerificationStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userType = Session::get('user_type');
        $userId = Session::get('user_id');
        
        // Only check verification for establishments and foodbanks
        // Consumers are excluded entirely
        if (!in_array($userType, ['establishment', 'foodbank'])) {
            return $next($request);
        }
        
        // Only restrict POST, PUT, DELETE, PATCH requests (actions)
        // Allow GET requests (viewing pages)
        if (!in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return $next($request);
        }
        
        // Get user model and check verification status
        $isVerified = false;
        
        if ($userType === 'establishment') {
            $user = Establishment::find($userId);
            if ($user) {
                $isVerified = $user->isVerified();
            }
        } elseif ($userType === 'foodbank') {
            $user = Foodbank::find($userId);
            if ($user) {
                $isVerified = $user->isVerified();
            }
        }
        
        // If not verified, return error response
        if (!$isVerified) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is not verified. Please wait for admin approval.',
                    'error' => 'unverified_account'
                ], 403);
            }
            
            return redirect()->back()->with('error', 'Your account is not verified. Please wait for admin approval.');
        }
        
        return $next($request);
    }
}
