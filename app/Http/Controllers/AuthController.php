<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\Consumer;
use App\Models\Establishment;
use App\Models\Foodbank;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.registration');
    }

    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        // Debug: Log incoming request
        \Log::info('Registration attempt started', [
            'role' => $request->role,
            'email' => $request->email,
            'username' => $request->username,
            'all_data' => $request->all()
        ]);
        
        try {
            // Prevent registration with admin email
            if ($request->email === 'admin@saveats.com') {
                throw ValidationException::withMessages([
                    'email' => ['This email address is reserved and cannot be used for registration.'],
                ]);
            }
            
            $request->validate([
                'role' => 'required|in:consumer,establishment,foodbank',
                'email' => 'required|email|unique:consumers,email|unique:establishments,email|unique:foodbanks,email',
                'username' => 'required|unique:consumers,username|unique:establishments,username|unique:foodbanks,username',
                'password' => 'required|min:8|confirmed',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Registration validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'role' => $request->role
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $userData = [
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ];

        $user = null;

        try {
            switch ($request->role) {
                case 'consumer':
                    try {
                        $request->validate([
                            'fname' => 'required|string|max:255',
                            'lname' => 'required|string|max:255',
                            'mname' => 'nullable|string|max:255',
                            'phone_no' => 'nullable|string|max:20',
                            'address' => 'nullable|string',
                        ]);
                    } catch (\Illuminate\Validation\ValidationException $e) {
                        if ($request->expectsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Consumer validation failed',
                                'errors' => $e->errors()
                            ], 422);
                        }
                        throw $e;
                    }

                    $consumerData = array_merge($userData, [
                        'fname' => $request->fname,
                        'lname' => $request->lname,
                        'mname' => $request->mname,
                        'phone_no' => $request->phone_no,
                        'address' => $request->address,
                    ]);
                    
                    \Log::info('Creating consumer with data:', $consumerData);
                    
                    $user = Consumer::create($consumerData);
                    break;

            case 'establishment':
                $request->validate([
                    'business_name' => 'required|string|max:255',
                    'owner_fname' => 'required|string|max:255',
                    'owner_lname' => 'required|string|max:255',
                    'phone_no' => 'nullable|string|max:20',
                    'address' => 'nullable|string',
                    'business_type' => 'nullable|string|max:255',
                    'birCertificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                ]);
                
                // Convert empty strings to null for nullable fields
                $establishmentData = [
                    'business_name' => $request->business_name,
                    'owner_fname' => $request->owner_fname,
                    'owner_lname' => $request->owner_lname,
                    'phone_no' => $request->phone_no ?: null,
                    'address' => $request->address ?: null,
                    'business_type' => $request->business_type ?: null,
                ];
                
                // Handle BIR file upload
                if ($request->hasFile('birCertificate')) {
                    try {
                        $birFile = $request->file('birCertificate');
                        $birFilePath = $birFile->store('bir-certificates', 'public');
                        
                        // Verify the file was actually stored
                        if (Storage::disk('public')->exists($birFilePath)) {
                            $establishmentData['bir_file'] = $birFilePath;
                        } else {
                            \Log::error('BIR file upload failed: File not found after storage', ['path' => $birFilePath]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('BIR file upload failed: ' . $e->getMessage());
                    }
                }

                $user = Establishment::create(array_merge($userData, $establishmentData));
                break;

            case 'foodbank':
                $request->validate([
                    'organization_name' => 'required|string|max:255',
                    'contact_person' => 'required|string|max:255',
                    'phone_no' => 'nullable|string|max:20',
                    'address' => 'nullable|string',
                    'registration_number' => 'nullable|string|max:255',
                ]);

                $user = Foodbank::create(array_merge($userData, [
                    'organization_name' => $request->organization_name,
                    'contact_person' => $request->contact_person,
                    'phone_no' => $request->phone_no,
                    'address' => $request->address,
                    'registration_number' => $request->registration_number,
                ]));
                break;
            }
        } catch (\Exception $e) {
            \Log::error('Exception during user creation:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'role' => $request->role,
                'email' => $request->email,
                'request_data' => $request->all()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration failed: ' . $e->getMessage(),
                    'error_type' => get_class($e),
                    'debug_data' => [
                        'role' => $request->role,
                        'email' => $request->email,
                        'has_fname' => $request->has('fname'),
                        'has_lname' => $request->has('lname')
                    ]
                ], 500);
            }
            
            return back()->with('error', 'Registration failed: ' . $e->getMessage());
        }

        if ($user) {
            // Log the user in
            $this->loginUser($user, $request->role);
            
            // Debug: Log successful creation
            \Log::info('User created successfully', [
                'user_id' => $user->getKey(),
                'role' => $request->role,
                'email' => $user->email
            ]);
            
            // Handle JSON requests (from JavaScript)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful! Welcome to SavEats!',
                    'redirect' => route('dashboard.' . $request->role)
                ]);
            }
            
            // Redirect to appropriate dashboard
            return redirect()->route('dashboard.' . $request->role)
                ->with('success', 'Registration successful! Welcome to SavEats!');
        }

        \Log::error('User creation failed - user object is null', [
            'role' => $request->role,
            'email' => $request->email
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ], 422);
        }
        
        return back()->with('error', 'Registration failed. Please try again.');
    }

    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required', // Can be email or username
            'password' => 'required',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        // Try to authenticate with each user type
        $userTypes = [
            'consumer' => Consumer::class,
            'establishment' => Establishment::class,
            'foodbank' => Foodbank::class,
        ];

        foreach ($userTypes as $type => $model) {
            $user = $model::where($loginField, $request->login)->first();
            
            if ($user && Hash::check($request->password, $user->password)) {
                $this->loginUser($user, $type);
                return redirect()->route('dashboard.' . $type)
                    ->with('success', 'Welcome back!');
            }
        }

        // Check admin users table as fallback (only email, no username)
        if ($loginField === 'email') {
            $adminUser = User::where('email', $request->login)->first();
            if ($adminUser && Hash::check($request->password, $adminUser->password)) {
                // Verify user has admin role
                if ($adminUser->role !== 'admin') {
                    throw ValidationException::withMessages([
                        'login' => ['The provided credentials are incorrect.'],
                    ]);
                }
                
                session([
                    'user_id' => $adminUser->id,
                    'user_type' => 'admin',
                    'user_email' => $adminUser->email,
                    'user_name' => $adminUser->name,
                    'authenticated' => true
                ]);
                return redirect()->route('dashboard.admin')
                    ->with('success', 'Welcome back, Admin!');
            }
        }

        throw ValidationException::withMessages([
            'login' => ['The provided credentials are incorrect.'],
        ]);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        // Clear our custom session data
        $request->session()->forget(['user_id', 'user_type', 'user_email', 'user_name', 'user_profile_picture', 'fname', 'lname', 'authenticated']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Login user and set session data
     */
    private function loginUser($user, $userType)
    {
        // Store user data in session manually instead of using Auth::login()
        // This avoids the sessions table user_id column type issue
        session([
            'user_id' => $user->getKey(),
            'user_type' => $userType,
            'user_email' => $user->email,
            'user_name' => $user->fname ?? $user->business_name ?? $user->organization_name ?? 'User',
            'fname' => $user->fname ?? '',
            'lname' => $user->lname ?? '',
            'user_profile_picture' => $user->profile_image ?? null,
            'authenticated' => true
        ]);
    }
}
