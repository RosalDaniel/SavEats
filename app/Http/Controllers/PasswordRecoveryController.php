<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;
use App\Models\Consumer;
use App\Models\Establishment;
use App\Models\Foodbank;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Models\SystemLog;

/**
 * Password Recovery Controller
 * 
 * Handles password reset requests for all user types: consumer, establishment, foodbank, and admin.
 * Uses secure token-based email reset links with 1-hour expiration.
 * All recovery attempts are logged to System Logs for security monitoring.
 */
class PasswordRecoveryController extends Controller
{
    /**
     * Show forgot password page
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle password reset request (email only)
     */
    public function requestReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $ipAddress = $request->ip();

        // Find user in all tables (including admin)
        $user = null;
        $userType = null;

        // Check admin first
        $adminUser = User::where('email', $email)->where('role', 'admin')->first();
        if ($adminUser) {
            $user = $adminUser;
            $userType = 'admin';
        } else {
            // Check consumer
            $user = Consumer::where('email', $email)->first();
            if ($user) {
                $userType = 'consumer';
            } else {
                // Check establishment
                $user = Establishment::where('email', $email)->first();
                if ($user) {
                    $userType = 'establishment';
                } else {
                    // Check foodbank
                    $user = Foodbank::where('email', $email)->first();
                    if ($user) {
                        $userType = 'foodbank';
                    }
                }
            }
        }

        if (!$user || !$userType) {
            // Don't reveal if user exists - security best practice
            $this->logRecoveryAttempt(null, null, 'email', 'failed', 'user_not_found', $ipAddress, $email);
            return redirect()->back()
                ->with('message', 'If an account exists with that email, a password reset link has been sent.');
        }

        // Rate limiting: max 3 requests per hour per email
        $key = 'password_reset_' . $email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $userId = $userType === 'admin' ? $user->id : $user->{$user->getKeyName()};
            $this->logRecoveryAttempt($userId, $userType, 'email', 'failed', 'rate_limited', $ipAddress, $email);
            return redirect()->back()
                ->with('error', 'Too many reset attempts. Please try again later.');
        }

        RateLimiter::hit($key, 3600); // 1 hour

        return $this->sendEmailResetLink($user, $userType, $email, $ipAddress);
    }

    /**
     * Send password reset link via email
     */
    private function sendEmailResetLink($user, $userType, $email, $ipAddress)
    {
        // Generate secure token
        $token = Str::random(64);
        
        // Get user ID based on type
        $userId = $userType === 'admin' 
            ? $user->id 
            : $user->{$user->getKeyName()};
        
        // Create reset token record - store hashed token for security
        PasswordResetToken::create([
            'user_type' => $userType,
            'user_id' => $userId,
            'email' => $email,
            'token' => Hash::make($token),
            'recovery_method' => 'email',
            'expires_at' => Carbon::now()->addHours(1), // Token expires in 1 hour
            'ip_address' => $ipAddress,
        ]);

        // Send email using Laravel Mail
        try {
            Mail::send('emails.password-reset', [
                'token' => $token,
                'user' => $user,
                'userType' => $userType,
                'resetUrl' => route('password-recovery.reset', ['token' => $token]),
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Password Reset Request - SavEats');
            });

            $this->logRecoveryAttempt($userId, $userType, 'email', 'success', 'email_sent', $ipAddress, $email);

            return redirect()->route('login')
                ->with('success', 'If an account exists with that email, a password reset link has been sent.');
        } catch (\Exception $e) {
            \Log::error('Password reset email failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->logRecoveryAttempt($userId, $userType, 'email', 'failed', 'email_send_failed: ' . $e->getMessage(), $ipAddress, $email);
            
            return redirect()->back()
                ->with('error', 'Failed to send reset email. Please try again later.');
        }
    }

    /**
     * Show reset password page
     */
    public function showResetPassword(Request $request, $token)
    {
        $ipAddress = $request->ip();
        
        // Find valid token - check all unused, non-expired tokens and verify hash
        $resetTokens = PasswordResetToken::where('used', false)
            ->where('expires_at', '>', now())
            ->where('recovery_method', 'email')
            ->get();
        
        $resetToken = null;
        foreach ($resetTokens as $tokenRecord) {
            // Check if the provided token matches the hashed token in database
            if (Hash::check($token, $tokenRecord->token)) {
                $resetToken = $tokenRecord;
                break;
            }
        }

        if (!$resetToken) {
            $this->logRecoveryAttempt(null, null, 'email', 'failed', 'invalid_token', $ipAddress);
            return redirect()->route('password-recovery.forgot')
                ->with('error', 'Invalid or expired reset token. Please request a new password reset link.');
        }

        if (!$resetToken->isValid()) {
            $this->logRecoveryAttempt($resetToken->user_id, $resetToken->user_type, 'email', 'failed', 'expired_token', $ipAddress, $resetToken->email);
            return redirect()->route('password-recovery.forgot')
                ->with('error', 'This reset link has expired. Please request a new password reset link.');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $resetToken->email,
        ]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Find valid token - check tokens for this email and verify hash
        $resetTokens = PasswordResetToken::where('email', $request->email)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->where('recovery_method', 'email')
            ->get();
        
        $resetToken = null;
        foreach ($resetTokens as $tokenRecord) {
            // Verify the token matches the hashed token in database
            if (Hash::check($request->token, $tokenRecord->token)) {
                $resetToken = $tokenRecord;
                break;
            }
        }

        if (!$resetToken) {
            $this->logRecoveryAttempt(null, null, 'email', 'failed', 'invalid_token', $request->ip(), $request->email);
            return redirect()->route('password-recovery.forgot')
                ->with('error', 'Invalid or expired reset token. Please request a new password reset link.');
        }

        if (!$resetToken->isValid()) {
            $this->logRecoveryAttempt($resetToken->user_id, $resetToken->user_type, 'email', 'failed', 'expired_token', $request->ip(), $request->email);
            return redirect()->route('password-recovery.forgot')
                ->with('error', 'This reset link has expired. Please request a new password reset link.');
        }

        // Get user
        $user = $this->getUserByType($resetToken->user_type, $resetToken->user_id);
        if (!$user) {
            $this->logRecoveryAttempt($resetToken->user_id, $resetToken->user_type, 'email', 'failed', 'user_not_found', $request->ip(), $request->email);
            return redirect()->route('password-recovery.forgot')
                ->with('error', 'User account not found. Please contact support.');
        }

        // Update password with secure hash
        $user->password = Hash::make($request->password);
        $user->save();

        // Mark token as used to prevent reuse
        $resetToken->markAsUsed();

        // Log successful password reset
        $this->logRecoveryAttempt($resetToken->user_id, $resetToken->user_type, 'email', 'success', 'password_reset', $request->ip(), $request->email);

        return redirect()->route('login')
            ->with('success', 'Your password has been reset successfully. Please login with your new password.');
    }


    /**
     * Send email verification
     */
    public function sendVerificationEmail($user, $userType)
    {
        $token = Str::random(64);
        
        // Store verification token (you can use a separate table or reuse password_reset_tokens)
        PasswordResetToken::create([
            'user_type' => $userType,
            'user_id' => $user->{$user->getKeyName()},
            'email' => $user->email,
            'token' => Hash::make($token),
            'recovery_method' => 'email_verification',
            'expires_at' => Carbon::now()->addDays(7), // Verification link valid for 7 days
            'ip_address' => request()->ip(),
        ]);

        try {
            Mail::send('emails.verify-email', [
                'token' => $token,
                'user' => $user,
                'userType' => $userType,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Verify Your Email - SavEats');
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request, $token)
    {
        // Find valid verification token
        $resetTokens = PasswordResetToken::where('recovery_method', 'email_verification')
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->get();
        
        $resetToken = null;
        foreach ($resetTokens as $tokenRecord) {
            if (Hash::check($token, $tokenRecord->token) || $tokenRecord->token === $token) {
                $resetToken = $tokenRecord;
                break;
            }
        }

        if (!$resetToken || !$resetToken->isValid()) {
            return redirect()->route('login')
                ->with('error', 'Invalid or expired verification link.');
        }

        // Get user and mark email as verified
        $user = $this->getUserByType($resetToken->user_type, $resetToken->user_id);
        if ($user && property_exists($user, 'email_verified_at')) {
            $user->email_verified_at = now();
            $user->save();
        }

        // Mark token as used
        $resetToken->markAsUsed();

        return redirect()->route('login')
            ->with('success', 'Your email has been verified. You can now login.');
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Check if it's an admin
        $adminUser = User::where('email', $request->email)->where('role', 'admin')->first();
        if ($adminUser) {
            return redirect()->back()
                ->with('error', 'Email verification is not available for this account type.');
        }

        // Find user
        $user = Consumer::where('email', $request->email)->first();
        $userType = 'consumer';
        
        if (!$user) {
            $user = Establishment::where('email', $request->email)->first();
            $userType = 'establishment';
        }
        
        if (!$user) {
            $user = Foodbank::where('email', $request->email)->first();
            $userType = 'foodbank';
        }

        if (!$user) {
            return redirect()->back()
                ->with('message', 'If an account exists with that email, a verification link has been sent.');
        }

        // Check if already verified
        if ($user->email_verified_at) {
            return redirect()->route('login')
                ->with('info', 'Your email is already verified.');
        }

        // Rate limiting
        $key = 'email_verification_' . $request->email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return redirect()->back()
                ->with('error', 'Too many verification requests. Please try again later.');
        }

        RateLimiter::hit($key, 3600);

        $this->sendVerificationEmail($user, $userType);

        return redirect()->back()
            ->with('success', 'Verification email sent. Please check your inbox.');
    }

    /**
     * Get user by type and ID
     */
    private function getUserByType($userType, $userId)
    {
        switch ($userType) {
            case 'consumer':
                return Consumer::find($userId);
            case 'establishment':
                return Establishment::find($userId);
            case 'foodbank':
                return Foodbank::find($userId);
            case 'admin':
                return User::where('id', $userId)->where('role', 'admin')->first();
            default:
                return null;
        }
    }

    /**
     * Log recovery attempt to system logs
     */
    private function logRecoveryAttempt($userId, $userType, $method, $status, $reason, $ipAddress, $email = null)
    {
        $description = sprintf(
            'Password recovery: %s via %s - %s%s',
            $status,
            $method,
            $reason,
            $userId ? " (User ID: {$userId}, Type: {$userType})" : ' (Email: ' . ($email ?? 'Unknown') . ')'
        );

        SystemLog::log(
            'password_recovery',
            'password_recovery_attempt',
            $description,
            $status === 'success' ? 'info' : ($status === 'failed' ? 'warning' : 'error'),
            $status,
            [
                'user_id' => $userId,
                'user_type' => $userType,
                'user_email' => $email,
                'recovery_method' => $method,
                'status' => $status,
                'reason' => $reason,
                'ip_address' => $ipAddress,
            ]
        );
    }
}

