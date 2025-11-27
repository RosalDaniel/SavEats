<?php

namespace App\Http\Controllers;

use App\Models\HelpCenterArticle;
use App\Models\TermsCondition;
use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    /**
     * Get published help articles
     */
    public function getArticles(Request $request)
    {
        $query = HelpCenterArticle::published()
            ->orderBy('display_order', 'asc')
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%')
                  ->orWhere('tags', 'like', '%' . $search . '%');
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Get all or paginate
        if ($request->has('limit')) {
            $articles = $query->limit($request->limit)->get();
        } else {
            $articles = $query->paginate(20);
        }

        return response()->json([
            'success' => true,
            'data' => $articles
        ]);
    }

    /**
     * Get single article by slug or ID
     */
    public function getArticle($identifier)
    {
        $article = HelpCenterArticle::published()
            ->where(function($query) use ($identifier) {
                $query->where('slug', $identifier)
                      ->orWhere('id', $identifier);
            })
            ->first();

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found'
            ], 404);
        }

        // Increment view count
        $article->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $article
        ]);
    }

    /**
     * Get article categories
     */
    public function getCategories()
    {
        $categories = HelpCenterArticle::published()
            ->select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get active Terms & Conditions
     */
    public function getTerms()
    {
        $terms = TermsCondition::getActive();

        if (!$terms) {
            return response()->json([
                'success' => false,
                'message' => 'Terms & Conditions not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $terms
        ]);
    }

    /**
     * Get active Privacy Policy
     */
    public function getPrivacy()
    {
        $privacy = PrivacyPolicy::getActive();

        if (!$privacy) {
            return response()->json([
                'success' => false,
                'message' => 'Privacy Policy not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $privacy
        ]);
    }

    /**
     * Display Terms & Conditions page
     */
    public function termsPage()
    {
        $terms = TermsCondition::getActive();
        $userType = session('user_type', 'consumer');

        return view('cms.terms', compact('terms', 'userType'));
    }

    /**
     * Display Privacy Policy page
     */
    public function privacyPage()
    {
        $privacy = PrivacyPolicy::getActive();
        $userType = session('user_type', 'consumer');

        return view('cms.privacy', compact('privacy', 'userType'));
    }
}
