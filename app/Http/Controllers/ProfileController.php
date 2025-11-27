<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Consumer;
use App\Models\Establishment;
use App\Models\Foodbank;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display the profile page
     */
    public function index()
    {
        $userData = $this->getUserData();
        $userType = Session::get('user_type');
        
        if (!$userData) {
            return redirect()->route('login')->with('error', 'Please login to access your profile.');
        }

        return view('profile.index', compact('userData', 'userType'));
    }

    /**
     * Update profile information
     */
    public function update(Request $request)
    {
        $userType = Session::get('user_type');
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Debug: Log what we're receiving
        \Log::info('Profile update request', [
            'has_profile_picture' => $request->hasFile('profile_picture'),
            'has_bir_file' => $request->hasFile('bir_file'),
            'has_first_name' => $request->has('first_name'),
            'has_last_name' => $request->has('last_name'),
            'has_email' => $request->has('email'),
            'has_phone' => $request->has('phone'),
            'has_address' => $request->has('address'),
            'has_username' => $request->has('username'),
            'all_input' => $request->all()
        ]);

        // Check if this is just a profile picture upload
        $isProfilePictureOnly = $request->hasFile('profile_picture') && 
                               !$request->has('first_name') && 
                               !$request->has('last_name') && 
                               !$request->has('email') && 
                               !$request->has('phone') && 
                               !$request->has('address') && 
                               !$request->has('username') &&
                               !$request->has('bir_file');

        // Check if this is a BIR file upload
        $isBirFileUpload = $request->hasFile('bir_file') && 
                          !$request->has('first_name') && 
                          !$request->has('profile_picture');
        
        if ($isProfilePictureOnly) {
            \Log::info('Using profile picture only validation');
            // Only validate profile picture for image-only uploads
            $validator = Validator::make($request->all(), [
                'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
        } elseif ($isBirFileUpload) {
            \Log::info('Using BIR file only validation');
            // Only validate BIR file for BIR-only uploads
            $validator = Validator::make($request->all(), [
                'bir_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
            ]);
        } else {
            \Log::info('Using full profile validation');
            // Validate all fields for full profile updates
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:500',
                'username' => 'required|string|max:255',
                'password' => 'nullable|string|min:8|confirmed',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'bir_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        try {
            $user = $this->getUserModel($userType, $userId);
            
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            if ($isProfilePictureOnly) {
                // Handle profile picture upload only
                try {
                    // Delete old profile picture if exists
                    if ($user->profile_image) {
                        Storage::disk('public')->delete($user->profile_image);
                    }
                    
                    $imagePath = $request->file('profile_picture')->store('profile-pictures', 'public');
                    
                    // Verify the file was actually stored
                    if (Storage::disk('public')->exists($imagePath)) {
                        $user->update(['profile_image' => $imagePath]);
                        
                        // Update session with new profile picture
                        Session::put('user_profile_picture', $imagePath);
                        
                        return response()->json([
                            'success' => true,
                            'message' => 'Profile picture updated successfully',
                            'data' => $user
                        ]);
                    } else {
                        \Log::error('Profile picture upload failed: File not found after storage', ['path' => $imagePath]);
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to upload profile picture'
                        ], 500);
                    }
                } catch (\Exception $e) {
                    \Log::error('Profile picture upload failed: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload profile picture: ' . $e->getMessage()
                    ], 500);
                }
            } elseif ($isBirFileUpload) {
                // Handle BIR file upload only
                try {
                    // Delete old BIR file if exists
                    if ($user->bir_file) {
                        Storage::disk('public')->delete($user->bir_file);
                    }
                    
                    $birFilePath = $request->file('bir_file')->store('bir-certificates', 'public');
                    
                    // Verify the file was actually stored
                    if (Storage::disk('public')->exists($birFilePath)) {
                        $user->update(['bir_file' => $birFilePath]);
                        
                        return response()->json([
                            'success' => true,
                            'message' => 'BIR file uploaded successfully',
                            'data' => $user
                        ]);
                    } else {
                        \Log::error('BIR file upload failed: File not found after storage', ['path' => $birFilePath]);
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to upload BIR file'
                        ], 500);
                    }
                } catch (\Exception $e) {
                    \Log::error('BIR file upload failed: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload BIR file: ' . $e->getMessage()
                    ], 500);
                }
            } else {
                // Handle full profile update
                $data = $request->all();
                
                // Handle profile picture upload
                if ($request->hasFile('profile_picture')) {
                    try {
                        // Delete old profile picture if exists
                        if ($user->profile_image) {
                            Storage::disk('public')->delete($user->profile_image);
                        }
                        
                        $imagePath = $request->file('profile_picture')->store('profile-pictures', 'public');
                        
                        // Verify the file was actually stored
                        if (Storage::disk('public')->exists($imagePath)) {
                            $data['profile_image'] = $imagePath;
                        } else {
                            \Log::error('Profile picture upload failed: File not found after storage', ['path' => $imagePath]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Profile picture upload failed: ' . $e->getMessage());
                    }
                }

                // Handle BIR file upload
                if ($request->hasFile('bir_file')) {
                    try {
                        // Delete old BIR file if exists
                        if ($user->bir_file) {
                            Storage::disk('public')->delete($user->bir_file);
                        }
                        
                        $birFilePath = $request->file('bir_file')->store('bir-certificates', 'public');
                        
                        // Verify the file was actually stored
                        if (Storage::disk('public')->exists($birFilePath)) {
                            $data['bir_file'] = $birFilePath;
                        } else {
                            \Log::error('BIR file upload failed: File not found after storage', ['path' => $birFilePath]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('BIR file upload failed: ' . $e->getMessage());
                    }
                }

                // Remove password from data if empty
                if (empty($data['password'])) {
                    unset($data['password']);
                } else {
                    $data['password'] = bcrypt($data['password']);
                }

                // Remove password_confirmation
                unset($data['password_confirmation']);
                unset($data['profile_picture']); // Remove the file object

                $user->update($data);

            // Update session data
            Session::put('user_name', $data['first_name'] . ' ' . $data['last_name']);
            Session::put('user_email', $data['email']);
            if (isset($data['profile_image'])) {
                Session::put('user_profile_picture', $data['profile_image']);
            }

                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'data' => $user
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Profile update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update profile',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user data from session
     */
    private function getUserData()
    {
        $userId = Session::get('user_id');
        $userType = Session::get('user_type');
        
        if (!$userId || !$userType) {
            return null;
        }

        $user = $this->getUserModel($userType, $userId);
        
        if (!$user) {
            return null;
        }

        return (object) [
            'id' => $user->id ?? $user->consumer_id ?? $user->establishment_id ?? $user->foodbank_id,
            'first_name' => $user->first_name ?? $user->fname ?? $user->owner_fname ?? '',
            'last_name' => $user->last_name ?? $user->lname ?? $user->owner_lname ?? '',
            'middle_name' => $user->middle_name ?? $user->mname ?? $user->owner_mname ?? '',
            'business_name' => $user->business_name ?? '',
            'bir_file' => $user->bir_file ?? '',
            'organization_name' => $user->organization_name ?? '',
            'email' => $user->email,
            'phone' => $user->phone ?? $user->phone_number ?? $user->contact_number ?? '',
            'address' => $user->address ?? $user->location ?? '',
            'username' => $user->username,
            'profile_picture' => $user->profile_image ?? $user->avatar ?? null,
            'user_type' => $userType
        ];
    }

    /**
     * Request account deletion
     */
    public function requestAccountDeletion(Request $request)
    {
        $userId = Session::get('user_id');
        $userType = Session::get('user_type');
        
        if (!$userId || !$userType) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Check if there's already a pending request
        $existingRequest = DB::table('account_deletion_requests')
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending deletion request. Please wait for admin approval.'
            ], 400);
        }

        try {
            $deletionRequestId = DB::table('account_deletion_requests')->insertGetId([
                'user_id' => $userId,
                'user_type' => $userType,
                'reason' => $request->input('reason'),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Get user name based on type
            $userName = 'User';
            if ($userType === 'consumer') {
                $user = \App\Models\Consumer::find($userId);
                $userName = $user ? ($user->fname . ' ' . $user->lname) : 'Consumer';
            } elseif ($userType === 'establishment') {
                $user = \App\Models\Establishment::find($userId);
                $userName = $user ? $user->business_name : 'Establishment';
            } elseif ($userType === 'foodbank') {
                $user = \App\Models\Foodbank::find($userId);
                $userName = $user ? $user->organization_name : 'Foodbank';
            }

            // Notify admin about account deletion request
            try {
                \App\Services\AdminNotificationService::notifyAccountDeletionRequest(
                    $deletionRequestId,
                    $userType,
                    $userId,
                    $userName,
                    $request->input('reason')
                );
            } catch (\Exception $e) {
                \Log::error('Failed to create admin notification for deletion request: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Account deletion request submitted successfully. An admin will review your request.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Account deletion request failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit deletion request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user model based on type
     */
    private function getUserModel($userType, $userId)
    {
        switch ($userType) {
            case 'consumer':
                return Consumer::find($userId);
            case 'establishment':
                return Establishment::find($userId);
            case 'foodbank':
                return Foodbank::find($userId);
            default:
                return null;
        }
    }
}