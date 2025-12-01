<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Consumer;
use App\Models\Establishment;
use App\Models\Foodbank;

/**
 * Middleware to block admin users from accessing password recovery routes.
 * 
 * SECURITY NOTE: Admin accounts must NOT be allowed to use public password recovery.
 * Admin recovery must be done only by system owner via CLI or direct DB update.
 * This is a critical security measure to prevent unauthorized admin access.
 */
class BlockAdminFromRecovery
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if request contains email or phone that belongs to an admin
        $email = $request->input('email');
        $phone = $request->input('phone_no') ?? $request->input('phone');
        
        if ($email) {
            // Check if email belongs to an admin user
            $adminUser = User::where('email', $email)->where('role', 'admin')->first();
            if ($adminUser) {
                // Return neutral message - never reveal it's an admin
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Password recovery is not available for this account type.'
                    ], 403);
                }
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Password recovery is not available for this account type.');
            }
        }
        
        // Note: Phone-based recovery doesn't need admin check since admins don't have phone numbers
        // in the users table. Phone recovery is only available for consumer/establishment/foodbank accounts.
        
        return $next($request);
    }
}
