<?php

namespace App\Http\Controllers;

use App\Models\HomepageBanner;
use App\Models\HelpCenterArticle;
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
        $bannersCount = HomepageBanner::count();
        $articlesCount = HelpCenterArticle::count();
        $termsCount = TermsCondition::count();
        $privacyCount = PrivacyPolicy::count();
        $announcementsCount = Announcement::count();

        return view('admin.cms.index', compact('user', 'bannersCount', 'articlesCount', 'termsCount', 'privacyCount', 'announcementsCount'));
    }

    // ============================================================================
    // HOMEPAGE BANNERS
    // ============================================================================

    /**
     * Get all banners
     */
    public function getBanners(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        // If ID is provided, return single banner
        if ($request->has('id')) {
            $banner = HomepageBanner::find($request->id);
            if (!$banner) {
                return response()->json(['success' => false, 'message' => 'Banner not found.'], 404);
            }
            return response()->json(['success' => true, 'data' => ['data' => [$banner]]]);
        }

        $query = HomepageBanner::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $banners = $query->orderBy('display_order', 'asc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(10);

        return response()->json(['success' => true, 'data' => $banners]);
    }

    /**
     * Store a new banner
     */
    public function storeBanner(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url|max:500',
            'link_url' => 'nullable|url|max:500',
            'display_order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $banner = HomepageBanner::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
                'link_url' => $validated['link_url'] ?? null,
                'display_order' => $validated['display_order'] ?? 0,
                'status' => $validated['status'],
                'start_date' => $validated['start_date'] ? Carbon::parse($validated['start_date']) : null,
                'end_date' => $validated['end_date'] ? Carbon::parse($validated['end_date']) : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Banner created successfully.',
                'data' => $banner
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create banner.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a banner
     */
    public function updateBanner(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $banner = HomepageBanner::find($id);
        if (!$banner) {
            return response()->json(['success' => false, 'message' => 'Banner not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url|max:500',
            'link_url' => 'nullable|url|max:500',
            'display_order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $banner->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
                'link_url' => $validated['link_url'] ?? null,
                'display_order' => $validated['display_order'] ?? 0,
                'status' => $validated['status'],
                'start_date' => $validated['start_date'] ? Carbon::parse($validated['start_date']) : null,
                'end_date' => $validated['end_date'] ? Carbon::parse($validated['end_date']) : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Banner updated successfully.',
                'data' => $banner->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a banner
     */
    public function deleteBanner($id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        try {
            $banner = HomepageBanner::find($id);
            if (!$banner) {
                return response()->json(['success' => false, 'message' => 'Banner not found.'], 404);
            }

            $banner->delete();

            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================================================
    // HELP CENTER ARTICLES
    // ============================================================================

    /**
     * Get all help articles
     */
    public function getArticles(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        // If ID is provided, return single article
        if ($request->has('id')) {
            $article = HelpCenterArticle::find($request->id);
            if (!$article) {
                return response()->json(['success' => false, 'message' => 'Article not found.'], 404);
            }
            return response()->json(['success' => true, 'data' => ['data' => [$article]]]);
        }

        $query = HelpCenterArticle::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        $articles = $query->orderBy('display_order', 'asc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(10);

        return response()->json(['success' => true, 'data' => $articles]);
    }

    /**
     * Store a new article
     */
    public function storeArticle(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'status' => 'required|in:published,draft,archived',
        ]);

        try {
            $article = HelpCenterArticle::create([
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']),
                'content' => $validated['content'],
                'category' => $validated['category'] ?? null,
                'tags' => $validated['tags'] ?? null,
                'display_order' => $validated['display_order'] ?? 0,
                'status' => $validated['status'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Article created successfully.',
                'data' => $article
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create article.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an article
     */
    public function updateArticle(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $article = HelpCenterArticle::find($id);
        if (!$article) {
            return response()->json(['success' => false, 'message' => 'Article not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'status' => 'required|in:published,draft,archived',
        ]);

        try {
            // Update slug if title changed
            if ($article->title !== $validated['title']) {
                $validated['slug'] = Str::slug($validated['title']);
            }

            $article->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Article updated successfully.',
                'data' => $article->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update article.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an article
     */
    public function deleteArticle($id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        try {
            $article = HelpCenterArticle::find($id);
            if (!$article) {
                return response()->json(['success' => false, 'message' => 'Article not found.'], 404);
            }

            $article->delete();

            return response()->json([
                'success' => true,
                'message' => 'Article deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete article.',
                'error' => $e->getMessage()
            ], 500);
        }
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
            'published_at' => 'nullable|date',
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
                'published_at' => $validated['published_at'] ? Carbon::parse($validated['published_at']) : ($validated['status'] === 'active' ? Carbon::now() : null),
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
            'published_at' => 'nullable|date',
        ]);

        try {
            // If setting as active, deactivate all other active terms
            if ($validated['status'] === 'active' && $terms->status !== 'active') {
                TermsCondition::where('status', 'active')->where('id', '!=', $id)->update(['status' => 'draft']);
            }

            $terms->update([
                'version' => $validated['version'],
                'content' => $validated['content'],
                'status' => $validated['status'],
                'published_at' => $validated['published_at'] ? Carbon::parse($validated['published_at']) : ($validated['status'] === 'active' && !$terms->published_at ? Carbon::now() : $terms->published_at),
            ]);

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
            'published_at' => 'nullable|date',
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
                'published_at' => $validated['published_at'] ? Carbon::parse($validated['published_at']) : ($validated['status'] === 'active' ? Carbon::now() : null),
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
            'published_at' => 'nullable|date',
        ]);

        try {
            // If setting as active, deactivate all other active policies
            if ($validated['status'] === 'active' && $policy->status !== 'active') {
                PrivacyPolicy::where('status', 'active')->where('id', '!=', $id)->update(['status' => 'draft']);
            }

            $policy->update([
                'version' => $validated['version'],
                'content' => $validated['content'],
                'status' => $validated['status'],
                'published_at' => $validated['published_at'] ? Carbon::parse($validated['published_at']) : ($validated['status'] === 'active' && !$policy->published_at ? Carbon::now() : $policy->published_at),
            ]);

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
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        try {
            $announcement = Announcement::create([
                'title' => $validated['title'],
                'message' => $validated['message'],
                'target_audience' => $validated['target_audience'],
                'status' => $validated['status'],
                'published_at' => $validated['published_at'] ? Carbon::parse($validated['published_at']) : now(),
                'expires_at' => $validated['expires_at'] ? Carbon::parse($validated['expires_at']) : null,
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
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        try {
            $announcement->update([
                'title' => $validated['title'],
                'message' => $validated['message'],
                'target_audience' => $validated['target_audience'],
                'status' => $validated['status'],
                'published_at' => $validated['published_at'] ? Carbon::parse($validated['published_at']) : null,
                'expires_at' => $validated['expires_at'] ? Carbon::parse($validated['expires_at']) : null,
            ]);

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

