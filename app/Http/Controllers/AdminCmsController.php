<?php

namespace App\Http\Controllers;

use App\Models\TermsCondition;
use App\Models\PrivacyPolicy;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminCmsController extends Controller
{
    /**
     * Get user data helper
     */
    private function getUserData()
    {
        return [
            'id' => session('user_id'),
            'username' => session('user_name'),
            'email' => session('user_email'),
            'type' => session('user_type'),
        ];
    }

    /**
     * CMS Dashboard - Main page with tabs
     */
    public function index()
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied. Please login as an admin.');
        }

        $user = $this->getUserData();
        
        // Get counts for each content type
        $termsCount = TermsCondition::count();
        $privacyCount = PrivacyPolicy::count();
        $announcementsCount = Announcement::count();

        return view('admin.cms.index', compact('user', 'termsCount', 'privacyCount', 'announcementsCount'));
    }

    // ============================================================================
    // TERMS & CONDITIONS
    // ============================================================================

    /**
     * Get all terms
     */
    public function getTerms(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        // If ID is provided, return single term
        if ($request->has('id')) {
            $term = TermsCondition::find($request->id);
            if (!$term) {
                return response()->json(['success' => false, 'message' => 'Terms not found.'], 404);
            }
            return response()->json(['success' => true, 'data' => ['data' => [$term]]]);
        }

        $query = TermsCondition::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('version', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $terms = $query->orderBy('published_at', 'desc')
                       ->orderBy('created_at', 'desc')
                       ->paginate(10);

        return response()->json(['success' => true, 'data' => $terms]);
    }

    /**
     * Store new terms
     */
    public function storeTerms(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $validated = $request->validate([
            'version' => 'required|string|max:50',
            'content' => 'required|string',
            'status' => 'required|in:active,draft',
        ]);

        try {
            // If setting as active, deactivate all other active terms
            if ($validated['status'] === 'active') {
                TermsCondition::where('status', 'active')->update(['status' => 'draft']);
            }

            $terms = TermsCondition::create([
                'version' => $validated['version'],
                'content' => $validated['content'],
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'active' ? Carbon::now() : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Terms & Conditions created successfully.',
                'data' => $terms
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create terms.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update terms
     */
    public function updateTerms(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $terms = TermsCondition::find($id);
        if (!$terms) {
            return response()->json(['success' => false, 'message' => 'Terms not found.'], 404);
        }

        $validated = $request->validate([
            'version' => 'required|string|max:50',
            'content' => 'required|string',
            'status' => 'required|in:active,draft',
        ]);

        try {
            // If setting as active, deactivate all other active terms
            if ($validated['status'] === 'active' && $terms->status !== 'active') {
                TermsCondition::where('status', 'active')->where('id', '!=', $id)->update(['status' => 'draft']);
            }

            // Check if there are changes
            $hasChanges = $terms->version !== $validated['version'] ||
                         $terms->content !== $validated['content'] ||
                         $terms->status !== $validated['status'];

            // Determine published_at: set to now if status is active and not already published, otherwise keep existing
            $publishedAt = $validated['status'] === 'active' && !$terms->published_at 
                ? Carbon::now() 
                : $terms->published_at;

            $updateData = [
                'version' => $validated['version'],
                'content' => $validated['content'],
                'status' => $validated['status'],
                'published_at' => $publishedAt,
            ];

            // Automatically update updated_at if there are changes
            if ($hasChanges) {
                $updateData['updated_at'] = Carbon::now();
            }

            $terms->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Terms & Conditions updated successfully.',
                'data' => $terms->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update terms.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete terms
     */
    public function deleteTerms($id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        try {
            $terms = TermsCondition::find($id);
            if (!$terms) {
                return response()->json(['success' => false, 'message' => 'Terms not found.'], 404);
            }

            $terms->delete();

            return response()->json([
                'success' => true,
                'message' => 'Terms & Conditions deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete terms.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================================================
    // PRIVACY POLICY
    // ============================================================================

    /**
     * Get all privacy policies
     */
    public function getPrivacy(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        // If ID is provided, return single policy
        if ($request->has('id')) {
            $policy = PrivacyPolicy::find($request->id);
            if (!$policy) {
                return response()->json(['success' => false, 'message' => 'Privacy Policy not found.'], 404);
            }
            return response()->json(['success' => true, 'data' => ['data' => [$policy]]]);
        }

        $query = PrivacyPolicy::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('version', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $policies = $query->orderBy('published_at', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);

        return response()->json(['success' => true, 'data' => $policies]);
    }

    /**
     * Store new privacy policy
     */
    public function storePrivacy(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $validated = $request->validate([
            'version' => 'required|string|max:50',
            'content' => 'required|string',
            'status' => 'required|in:active,draft',
        ]);

        try {
            // If setting as active, deactivate all other active policies
            if ($validated['status'] === 'active') {
                PrivacyPolicy::where('status', 'active')->update(['status' => 'draft']);
            }

            $policy = PrivacyPolicy::create([
                'version' => $validated['version'],
                'content' => $validated['content'],
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'active' ? Carbon::now() : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Privacy Policy created successfully.',
                'data' => $policy
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create privacy policy.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update privacy policy
     */
    public function updatePrivacy(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $policy = PrivacyPolicy::find($id);
        if (!$policy) {
            return response()->json(['success' => false, 'message' => 'Privacy Policy not found.'], 404);
        }

        $validated = $request->validate([
            'version' => 'required|string|max:50',
            'content' => 'required|string',
            'status' => 'required|in:active,draft',
        ]);

        try {
            // If setting as active, deactivate all other active policies
            if ($validated['status'] === 'active' && $policy->status !== 'active') {
                PrivacyPolicy::where('status', 'active')->where('id', '!=', $id)->update(['status' => 'draft']);
            }

            // Check if there are changes
            $hasChanges = $policy->version !== $validated['version'] ||
                         $policy->content !== $validated['content'] ||
                         $policy->status !== $validated['status'];

            // Determine published_at: set to now if status is active and not already published, otherwise keep existing
            $publishedAt = $validated['status'] === 'active' && !$policy->published_at 
                ? Carbon::now() 
                : $policy->published_at;

            $updateData = [
                'version' => $validated['version'],
                'content' => $validated['content'],
                'status' => $validated['status'],
                'published_at' => $publishedAt,
            ];

            // Automatically update updated_at if there are changes
            if ($hasChanges) {
                $updateData['updated_at'] = Carbon::now();
            }

            $policy->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Privacy Policy updated successfully.',
                'data' => $policy->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update privacy policy.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete privacy policy
     */
    public function deletePrivacy($id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        try {
            $policy = PrivacyPolicy::find($id);
            if (!$policy) {
                return response()->json(['success' => false, 'message' => 'Privacy Policy not found.'], 404);
            }

            $policy->delete();

            return response()->json([
                'success' => true,
                'message' => 'Privacy Policy deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete privacy policy.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================================================
    // ANNOUNCEMENTS
    // ============================================================================

    /**
     * Get all announcements
     */
    public function getAnnouncements(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        // If ID is provided, return single announcement
        if ($request->has('id')) {
            $announcement = Announcement::find($request->id);
            if (!$announcement) {
                return response()->json(['success' => false, 'message' => 'Announcement not found.'], 404);
            }
            return response()->json(['success' => true, 'data' => ['data' => [$announcement]]]);
        }

        $query = Announcement::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('message', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by audience
        if ($request->has('audience') && $request->audience && $request->audience !== 'all') {
            $query->where('target_audience', $request->audience);
        }

        $announcements = $query->orderBy('created_at', 'desc')
                              ->paginate(10);

        return response()->json(['success' => true, 'data' => $announcements]);
    }

    /**
     * Store a new announcement
     */
    public function storeAnnouncement(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target_audience' => 'required|in:all,consumer,establishment,foodbank',
            'status' => 'required|in:active,inactive,archived',
            'expires_at' => 'nullable|date',
        ]);

        try {
            // Automatically set published_at when status is active
            $publishedAt = ($validated['status'] === 'active') ? Carbon::now() : null;

            // Validate expires_at is after published_at if both exist
            $expiresAt = $validated['expires_at'] ? Carbon::parse($validated['expires_at']) : null;
            if ($expiresAt && $publishedAt && $expiresAt->lte($publishedAt)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expires date must be after published date.',
                ], 422);
            }

            $announcement = Announcement::create([
                'title' => $validated['title'],
                'message' => $validated['message'],
                'target_audience' => $validated['target_audience'],
                'status' => $validated['status'],
                'published_at' => $publishedAt,
                'expires_at' => $expiresAt,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Announcement created successfully.',
                'data' => $announcement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create announcement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an announcement
     */
    public function updateAnnouncement(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $announcement = Announcement::find($id);
        if (!$announcement) {
            return response()->json(['success' => false, 'message' => 'Announcement not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target_audience' => 'required|in:all,consumer,establishment,foodbank',
            'status' => 'required|in:active,inactive,archived',
            'expires_at' => 'nullable|date',
        ]);

        try {
            // Check if there are changes
            $hasChanges = $announcement->title !== $validated['title'] ||
                         $announcement->message !== $validated['message'] ||
                         $announcement->target_audience !== $validated['target_audience'] ||
                         $announcement->status !== $validated['status'] ||
                         ($announcement->expires_at ? $announcement->expires_at->format('Y-m-d H:i:s') : null) !== ($validated['expires_at'] ?? null);

            // Automatically set published_at when status changes to active and not already published
            $publishedAt = ($validated['status'] === 'active' && !$announcement->published_at) 
                ? Carbon::now() 
                : $announcement->published_at;

            // Validate expires_at is after published_at if both exist
            $expiresAt = $validated['expires_at'] ? Carbon::parse($validated['expires_at']) : null;
            if ($expiresAt && $publishedAt && $expiresAt->lte($publishedAt)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expires date must be after published date.',
                ], 422);
            }

            $updateData = [
                'title' => $validated['title'],
                'message' => $validated['message'],
                'target_audience' => $validated['target_audience'],
                'status' => $validated['status'],
                'published_at' => $publishedAt,
                'expires_at' => $expiresAt,
            ];

            // Automatically update updated_at if there are changes
            if ($hasChanges) {
                $updateData['updated_at'] = Carbon::now();
            }

            $announcement->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully.',
                'data' => $announcement->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update announcement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an announcement
     */
    public function deleteAnnouncement($id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        try {
            $announcement = Announcement::find($id);
            if (!$announcement) {
                return response()->json(['success' => false, 'message' => 'Announcement not found.'], 404);
            }

            $announcement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Announcement deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete announcement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

