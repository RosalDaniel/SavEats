<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consumer;
use App\Models\Establishment;
use App\Models\Foodbank;
use App\Models\User;
use App\Models\FoodListing;
use App\Models\Order;
use App\Models\Review;
use App\Models\DonationRequest;
use App\Models\Donation;
use App\Models\Announcement;
use App\Services\NotificationService;
use App\Services\DonationRequestService;
use App\Services\DashboardCacheService;
use App\Models\Notification;
use App\Models\DeletionRequest;
use App\Models\StockLedger;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    /**
     * Get user data from session and appropriate model
     */
    private function getUserData()
    {
        $userId = session('user_id');
        $userType = session('user_type');
        
        if (!$userId || !$userType) {
            return null;
        }

        switch ($userType) {
            case 'consumer':
                return Consumer::find($userId);
            case 'establishment':
                return Establishment::find($userId);
            case 'foodbank':
                return Foodbank::find($userId);
            case 'admin':
                return User::find($userId);
            default:
                return null;
        }
    }

    /**
     * Check if the current foodbank is verified
     */
    private function checkFoodbankVerification()
    {
        $foodbankId = session('user_id');
        if (!$foodbankId) {
            return false;
        }
        
        $foodbank = Foodbank::find($foodbankId);
        return $foodbank && $foodbank->isVerified();
    }

    /**
     * Show consumer dashboard
     */
    public function consumer()
    {
        // Verify user is a consumer
        if (session('user_type') !== 'consumer') {
            return redirect()->route('login')->with('error', 'Access denied. Please login as a consumer.');
        }
        
        $user = $this->getUserData();
        $consumerId = session('user_id');
        
        // Get cached statistics
        $stats = $consumerId ? DashboardCacheService::getConsumerStats($consumerId) : [
            'total_savings' => 0,
            'orders_count' => 0,
            'food_rescued' => 0,
            'rated_orders_count' => 0,
        ];
        
        $totalSavings = $stats['total_savings'];
        $ordersCount = $stats['orders_count'];
        $foodRescued = $stats['food_rescued'];
        $ratedOrdersCount = $stats['rated_orders_count'];
        
        // Get upcoming order (pending or accepted status) - not cached as it changes frequently
        $upcomingOrder = null;
        if ($consumerId) {
            $upcomingOrder = Order::with(['foodListing', 'establishment'])
                ->where('consumer_id', $consumerId)
                ->whereIn('status', ['pending', 'accepted'])
                ->orderBy('created_at', 'desc')
                ->first();
        }
        
        // Calculate badge progress based on meals saved (completed orders quantity)
        $badgeData = null;
        if ($consumerId) {
            $mealsSaved = $foodRescued; // Total quantity from completed orders
            
            // Define badges with their requirements
            $badges = [
                [
                    'name' => 'Meal Rescuer',
                    'requirement' => 5,
                    'description' => 'Saved 5 meals',
                ],
                [
                    'name' => 'Food Hero',
                    'requirement' => 10,
                    'description' => 'Saved 10 meals',
                ],
                [
                    'name' => 'Eco Starter',
                    'requirement' => 20,
                    'description' => 'Saved 20 meals',
                ],
                [
                    'name' => 'Super Saver',
                    'requirement' => 30,
                    'description' => 'Saved 30 meals',
                ],
            ];
            
            // Find the current badge (highest completed badge)
            $currentBadge = null;
            $nextBadge = null;
            
            foreach ($badges as $index => $badge) {
                if ($mealsSaved >= $badge['requirement']) {
                    $currentBadge = $badge;
                    // Get next badge if exists
                    if (isset($badges[$index + 1])) {
                        $nextBadge = $badges[$index + 1];
                    }
                } else {
                    if (!$currentBadge) {
                        $nextBadge = $badge;
                    }
                    break;
                }
            }
            
            // If no badge completed yet, show first badge as next
            if (!$currentBadge && !$nextBadge) {
                $nextBadge = $badges[0];
            }
            
            // Calculate progress for display
            if ($currentBadge && $nextBadge) {
                // Show progress to next badge
                $progress = min(100, ($mealsSaved / $nextBadge['requirement']) * 100);
                $badgeData = [
                    'current' => $currentBadge,
                    'next' => $nextBadge,
                    'progress' => round($progress, 0),
                    'meals_saved' => $mealsSaved,
                    'next_requirement' => $nextBadge['requirement'],
                    'status' => 'in_progress',
                ];
            } elseif ($currentBadge && !$nextBadge) {
                // All badges completed
                $badgeData = [
                    'current' => $currentBadge,
                    'next' => null,
                    'progress' => 100,
                    'meals_saved' => $mealsSaved,
                    'next_requirement' => null,
                    'status' => 'completed',
                ];
            } else {
                // No badge completed, show first badge progress
                $progress = min(100, ($mealsSaved / $nextBadge['requirement']) * 100);
                $badgeData = [
                    'current' => null,
                    'next' => $nextBadge,
                    'progress' => round($progress, 0),
                    'meals_saved' => $mealsSaved,
                    'next_requirement' => $nextBadge['requirement'],
                    'status' => 'in_progress',
                ];
            }
        }
        
        // Get random food listings with discounts (best deals) - cached
        $bestDeals = Cache::remember('dashboard:consumer:best_deals', 5 * 60, function () {
            return FoodListing::where('status', 'active')
                ->where('discount_percentage', '>', 0)
                ->where('expiry_date', '>=', now()->toDateString())
                ->orderBy('discount_percentage', 'desc')
                ->inRandomOrder()
                ->limit(2)
                ->get()
                ->map(function ($item) {
                    // Calculate available stock
                    $availableStock = max(0, $item->quantity - ($item->reserved_stock ?? 0));
                    
                    // Calculate discounted price
                    $originalPrice = (float) $item->original_price;
                    $discountPercentage = (float) ($item->discount_percentage ?? 0);
                    $discountedPrice = $originalPrice * (1 - ($discountPercentage / 100));
                    
                    // Get image URL
                    $imageUrl = null;
                    if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
                        $imageUrl = Storage::url($item->image_path);
                    }
                    
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'quantity' => $availableStock,
                        'original_price' => $item->original_price,
                        'discounted_price' => $discountedPrice,
                        'discount_percentage' => round($item->discount_percentage),
                        'image_url' => $imageUrl,
                    ];
                });
        });
        
        return view('consumer.dashboard', compact(
            'user', 
            'bestDeals',
            'totalSavings',
            'ordersCount',
            'foodRescued',
            'ratedOrdersCount',
            'upcomingOrder',
            'badgeData'
        ));
    }

    /**
     * Show establishment dashboard
     */
    public function establishment()
    {
        // Verify user is an establishment
        if (session('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Access denied. Please login as an establishment.');
        }
        
        $user = $this->getUserData();
        return view('establishment.dashboard', compact('user'));
    }

    /**
     * Show foodbank dashboard
     */
    public function foodbank()
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return redirect()->route('login')->with('error', 'Access denied. Please login as a foodbank.');
        }
        
        $user = $this->getUserData();
        $foodbankId = session('user_id');
        $foodbank = Foodbank::find($foodbankId);
        $isVerified = $foodbank && $foodbank->isVerified();
        
        // Get cached statistics
        $stats = DashboardCacheService::getFoodbankStats($foodbankId);
        $activeRequests = $stats['active_requests'];
        $businessPartnered = $stats['business_partnered'];
        $donationsReceived = $stats['donations_received'];
        
        // Get recent donations (latest 2) - not cached as it changes frequently
        $recentDonations = Donation::where('foodbank_id', $foodbankId)
            ->with(['establishment', 'donationRequest'])
            ->orderByRaw('COALESCE(collected_at, created_at) DESC')
            ->limit(2)
            ->get()
            ->map(function ($donation) {
                $establishment = $donation->establishment;
                
                // Use collected_at if available, otherwise use created_at for display
                $displayDate = $donation->collected_at ?? $donation->created_at;
                
                // Format scheduled time
                $timeDisplay = 'N/A';
                if ($donation->scheduled_time) {
                    $timeDisplay = is_string($donation->scheduled_time) 
                        ? substr($donation->scheduled_time, 0, 5) 
                        : $donation->scheduled_time->format('H:i');
                }
                
                return [
                    'id' => $donation->donation_id,
                    'donation_number' => $donation->donation_number,
                    'item_name' => $donation->item_name,
                    'quantity' => $donation->quantity,
                    'unit' => $donation->unit ?? 'pcs',
                    'establishment_name' => $establishment ? $establishment->business_name : 'Unknown',
                    'establishment_id' => $donation->establishment_id,
                    'category' => $donation->item_category,
                    'description' => $donation->description,
                    'expiry_date' => $donation->expiry_date ? $donation->expiry_date->format('Y-m-d') : null,
                    'expiry_date_display' => $donation->expiry_date ? $donation->expiry_date->format('F d, Y') : 'N/A',
                    'status' => $donation->status,
                    'status_display' => ucfirst(str_replace('_', ' ', $donation->status)),
                    'pickup_method' => $donation->pickup_method,
                    'pickup_method_display' => ucfirst($donation->pickup_method),
                    'scheduled_date' => $donation->scheduled_date ? $donation->scheduled_date->format('Y-m-d') : null,
                    'scheduled_date_display' => $donation->scheduled_date ? $donation->scheduled_date->format('F d, Y') : 'N/A',
                    'scheduled_time' => $timeDisplay,
                    'created_at' => $donation->created_at->format('Y-m-d H:i:s'),
                    'created_at_display' => $donation->created_at->format('F d, Y g:i A'),
                    'collected_at' => $donation->collected_at ? $donation->collected_at->format('Y-m-d H:i:s') : null,
                    'collected_at_display' => $donation->collected_at ? $donation->collected_at->format('F d, Y g:i A') : 'N/A',
                    'handler_name' => $donation->handler_name ?? 'N/A',
                    'establishment_notes' => $donation->establishment_notes,
                    'foodbank_notes' => $donation->foodbank_notes,
                    'is_urgent' => $donation->is_urgent ?? false,
                    'is_nearing_expiry' => $donation->is_nearing_expiry ?? false,
                    'formatted_date' => $displayDate->format('l - g:i A'),
                ];
            });
        
        // Get cached weekly chart data
        $weeklyData = DashboardCacheService::getFoodbankWeeklyData($foodbankId);
        
        return view('foodbank.dashboard', compact(
            'user', 
            'weeklyData', 
            'activeRequests', 
            'businessPartnered', 
            'donationsReceived',
            'recentDonations',
            'isVerified'
        ));
    }

    /**
     * Show announcements page for foodbank
     */
    public function foodbankAnnouncements()
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return redirect()->route('login')->with('error', 'Access denied. Please login as a foodbank.');
        }
        
        $user = $this->getUserData();
        
        // Fetch announcements for foodbanks (all + foodbank-specific)
        $announcements = Announcement::where('status', 'active')
            ->where(function($query) {
                $query->where('target_audience', 'all')
                      ->orWhere('target_audience', 'foodbank');
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Group announcements by date
        $groupedAnnouncements = $this->groupAnnouncementsByDate($announcements);
        
        return view('foodbank.announcements', compact('user', 'announcements', 'groupedAnnouncements'));
    }
    
    /**
     * Group announcements by date (Today, Yesterday, A week ago, A month ago)
     */
    private function groupAnnouncementsByDate($announcements)
    {
        $now = now();
        $today = $now->copy()->startOfDay();
        $yesterday = $today->copy()->subDay();
        $weekAgo = $today->copy()->subWeek();
        $monthAgo = $today->copy()->subMonth();
        
        $grouped = [
            'today' => [],
            'yesterday' => [],
            'week' => [],
            'month' => []
        ];
        
        foreach ($announcements as $announcement) {
            $createdAt = $announcement->created_at;
            
            // Use Carbon's built-in date comparison methods
            if ($createdAt->isToday()) {
                $grouped['today'][] = $announcement;
            } elseif ($createdAt->isYesterday()) {
                $grouped['yesterday'][] = $announcement;
            } elseif ($createdAt->gte($weekAgo)) {
                // Created within the last week (but not today or yesterday)
                $grouped['week'][] = $announcement;
            } elseif ($createdAt->gte($monthAgo)) {
                // Created within the last month (but not within the last week)
                $grouped['month'][] = $announcement;
            }
        }
        
        return $grouped;
    }

    /**
     * Show help center for foodbank
     */
    public function foodbankHelp()
    {
        $foodbankId = session('user_id');
        $foodbank = Foodbank::find($foodbankId);
        $isVerified = $foodbank && $foodbank->isVerified();
        return view('foodbank.help');
    }

    /**
     * Show settings page for foodbank
     */
    public function foodbankSettings()
    {
        $userData = $this->getUserData();
        $foodbankId = session('user_id');
        $foodbank = Foodbank::find($foodbankId);
        $isVerified = $foodbank && $foodbank->isVerified();
        return view('foodbank.settings', compact('userData'));
    }

    /**
     * Show donation request page for foodbank
     */
    public function donationRequest()
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return redirect()->route('login')->with('error', 'Access denied. Please login as a foodbank.');
        }
        
        $user = $this->getUserData();
        $foodbankId = session('user_id');
        $foodbank = Foodbank::find($foodbankId);
        $isVerified = $foodbank && $foodbank->isVerified();
        
        // Ensure foodbank_id is a string (UUID)
        if (!is_string($foodbankId)) {
            $foodbankId = (string) $foodbankId;
        }
        
        // Fetch INCOMING requests (pending-type statuses - newly submitted by establishments)
        // Include both 'pending' and 'pending_confirmation' statuses
        // Only show pickup requests (delivery is no longer supported)
        $incomingRequestsQuery = DonationRequest::where('foodbank_id', $foodbankId)
            ->whereNotNull('establishment_id')
            ->whereIn('status', [
                DonationRequestService::STATUS_PENDING,
                DonationRequestService::STATUS_PENDING_CONFIRMATION
            ])
            ->where(function($query) {
                $query->where('pickup_method', 'pickup')
                      ->orWhereNull('pickup_method'); // Legacy records without pickup_method
            });
        
        // Log ALL donation requests for this foodbank to debug (including all statuses)
        $allRequests = DonationRequest::where('foodbank_id', $foodbankId)
            ->whereNotNull('establishment_id')
            ->orderBy('created_at', 'desc')
            ->get(['donation_request_id', 'status', 'item_name', 'created_at', 'establishment_id']);
        
        \Log::info('All donation requests for foodbank (debug)', [
            'foodbank_id' => $foodbankId,
            'total_count' => $allRequests->count(),
            'requests' => $allRequests->map(function($r) {
                return [
                    'id' => $r->donation_request_id,
                    'status' => $r->status,
                    'item_name' => $r->item_name,
                    'created_at' => $r->created_at->format('Y-m-d H:i:s'),
                    'establishment_id' => $r->establishment_id,
                ];
            })->toArray(),
        ]);
        
        // Also check for the specific ID the user mentioned
        $specificRequest = DonationRequest::where('donation_request_id', '079311d8-9f7b-4df0-87d8-8de2da60cf0f')->first();
        if ($specificRequest) {
            \Log::info('Found specific donation request', [
                'id' => $specificRequest->donation_request_id,
                'status' => $specificRequest->status,
                'foodbank_id' => $specificRequest->foodbank_id,
                'establishment_id' => $specificRequest->establishment_id,
                'item_name' => $specificRequest->item_name,
            ]);
        } else {
            \Log::warning('DonationRequest 079311d8-9f7b-4df0-87d8-8de2da60cf0f NOT FOUND in database');
        }
        
        // Log query for debugging (can be removed in production)
        $rawCount = $incomingRequestsQuery->count();
        \Log::info('Incoming donation requests query', [
            'foodbank_id' => $foodbankId,
            'raw_count' => $rawCount,
            'statuses_checked' => [DonationRequestService::STATUS_PENDING, DonationRequestService::STATUS_PENDING_CONFIRMATION],
        ]);
        
        $incomingRequests = $incomingRequestsQuery
            ->with(['establishment'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        \Log::info('Fetched incoming requests', [
            'count' => $incomingRequests->count(),
            'request_ids' => $incomingRequests->pluck('donation_request_id')->toArray(),
        ]);
        
        $incomingRequests = $incomingRequests->map(function($request) {
                try {
                    return DonationRequestService::formatRequestData($request);
                } catch (\Exception $e) {
                    \Log::error('Error formatting donation request data', [
                        'request_id' => $request->donation_request_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return null;
                }
            })
            ->filter(function($request) {
                return $request !== null;
            })
            ->values()
            ->toArray();
        
        \Log::info('Formatted incoming requests', [
            'count' => count($incomingRequests),
            'request_ids' => array_column($incomingRequests, 'id'),
        ]);
        
        // Fetch ACCEPTED requests (accepted by foodbank, awaiting pickup confirmation)
        // Only show pickup requests (delivery is no longer supported)
        $acceptedRequests = DonationRequest::where('foodbank_id', $foodbankId)
            ->whereNotNull('establishment_id')
            ->where('status', DonationRequestService::STATUS_ACCEPTED)
            ->where(function($query) {
                $query->where('pickup_method', 'pickup')
                      ->orWhereNull('pickup_method'); // Legacy records without pickup_method
            })
            ->with(['establishment'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($request) {
                return DonationRequestService::formatRequestData($request);
            })
            ->toArray();
        
        // Fetch DECLINED requests
        // Only show pickup requests (delivery is no longer supported)
        $declinedRequests = DonationRequest::where('foodbank_id', $foodbankId)
            ->whereNotNull('establishment_id')
            ->where('status', DonationRequestService::STATUS_DECLINED)
            ->where(function($query) {
                $query->where('pickup_method', 'pickup')
                      ->orWhereNull('pickup_method'); // Legacy records without pickup_method
            })
            ->with(['establishment'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($request) {
                return DonationRequestService::formatRequestData($request);
            })
            ->toArray();
        
        // Fetch COMPLETED requests
        // Only show pickup requests (delivery is no longer supported)
        $completedRequests = DonationRequest::where('foodbank_id', $foodbankId)
            ->whereNotNull('establishment_id')
            ->where('status', DonationRequestService::STATUS_COMPLETED)
            ->where(function($query) {
                $query->where('pickup_method', 'pickup')
                      ->orWhereNull('pickup_method'); // Legacy records without pickup_method
            })
            ->with(['establishment', 'donation'])
            ->orderBy('fulfilled_at', 'desc')
            ->get()
            ->map(function($request) {
                return DonationRequestService::formatRequestData($request);
            })
            ->toArray();
        
        // Legacy: Fetch foodbank's own donation requests (foodbank requesting donations from establishments)
        // This is separate from establishment-initiated requests
        // Only show pickup requests (delivery is no longer supported)
        $foodbankDonationRequests = DonationRequest::where('foodbank_id', $foodbankId)
            ->whereNull('establishment_id')
            ->where(function($query) {
                $query->where('pickup_method', 'pickup')
                      ->orWhereNull('pickup_method'); // Legacy records without pickup_method
            })
            ->orderBy('created_at', 'desc')
            ->with(['donation', 'fulfilledBy'])
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->donation_request_id,
                    'foodType' => $request->item_name,
                    'quantity' => $request->quantity,
                    'matches' => $request->matches ?? 0,
                    'status' => $request->status,
                    'donation_id' => $request->donation_id,
                    'fulfilled_at' => $request->fulfilled_at,
                    'pickup_method' => 'pickup', // Always pickup
                    'establishment_name' => $request->fulfilledBy ? $request->fulfilledBy->business_name : null,
                    'establishment_id' => $request->fulfilled_by_establishment_id,
                ];
            })
            ->toArray();
        
        return view('foodbank.donation-requests', compact(
            'user', 
            'incomingRequests', 
            'acceptedRequests', 
            'declinedRequests', 
            'completedRequests',
            'isVerified'
        ));
    }

    /**
     * Show donation requests list page (foodbank's own published requests AND establishment-submitted requests)
     */
    public function donationRequestsList()
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return redirect()->route('login')->with('error', 'Access denied. Please login as a foodbank.');
        }
        
        $user = $this->getUserData();
        $foodbankId = session('user_id');
        $foodbank = Foodbank::find($foodbankId);
        $isVerified = $foodbank && $foodbank->isVerified();
        
        // Fetch ALL donation requests for this foodbank (both foodbank's own and establishment-submitted)
        // Always read fresh from database - no caching
        $foodbankDonationRequests = DonationRequest::where('foodbank_id', $foodbankId)
            ->where(function($query) {
                $query->where('pickup_method', 'pickup')
                      ->orWhereNull('pickup_method'); // Legacy records without pickup_method
            })
            ->orderBy('created_at', 'desc')
            ->with(['donation', 'fulfilledBy', 'establishment'])
            ->get()
            ->map(function ($request) {
                $data = DonationRequestService::formatRequestData($request);
                // Add additional fields for display
                $data['foodType'] = $data['item_name'];
                // Mark if it's a foodbank's own request or establishment-submitted
                $data['is_foodbank_request'] = is_null($request->establishment_id);
                $data['establishment_name'] = $request->establishment ? $request->establishment->business_name : null;
                return $data;
            })
            ->toArray();
        
        return view('foodbank.donation-requests-list', compact(
            'user',
            'foodbankDonationRequests'
        ));
    }

    /**
     * Fetch fresh donation requests data for the List page (AJAX endpoint)
     */
    public function fetchDonationRequestsList()
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as a foodbank.'
            ], 403);
        }
        
        $foodbankId = session('user_id');
        
        // Fetch ALL donation requests for this foodbank (both foodbank's own and establishment-submitted)
        // Always read fresh from database - no caching
        // Re-query to ensure we get the absolute latest data
        $allDonationRequests = DonationRequest::where('foodbank_id', $foodbankId)
            ->where(function($query) {
                $query->where('pickup_method', 'pickup')
                      ->orWhereNull('pickup_method'); // Legacy records without pickup_method
            })
            ->orderBy('created_at', 'desc')
            ->with(['donation', 'fulfilledBy', 'establishment'])
            ->get() // Fresh query - no cache
            ->map(function ($request) {
                // Reload the model to ensure we have the latest status
                $request->refresh();
                $data = DonationRequestService::formatRequestData($request);
                // Add additional fields for display
                $data['foodType'] = $data['item_name'];
                // Mark if it's a foodbank's own request or establishment-submitted
                $data['is_foodbank_request'] = is_null($request->establishment_id);
                $data['establishment_name'] = $request->establishment ? $request->establishment->business_name : null;
                return $data;
            })
            ->toArray();
        
        return response()->json([
            'success' => true,
            'data' => $allDonationRequests
        ], 200);
    }

    /**
     * Store a new donation request
     */
    public function storeDonationRequest(Request $request)
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as a foodbank.'
            ], 403);
        }

        // Check verification status
        if (!$this->checkFoodbankVerification()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not verified. Please wait for admin approval.',
                'error' => 'unverified_account'
            ], 403);
        }

        $foodbankId = session('user_id');

        // Validate the request
        $validated = $request->validate([
            'itemName' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'contactName' => 'required|string|max:255',
            'phoneNumber' => 'required|string',
            'email' => 'required|email|max:255',
        ]);

        try {
            // Create the donation request (pickup only - establishment's address will be used)
            $donationRequest = DonationRequest::create([
                'foodbank_id' => $foodbankId,
                'item_name' => $validated['itemName'],
                'quantity' => $validated['quantity'],
                'category' => $validated['category'],
                'description' => $validated['description'] ?? null,
                'distribution_zone' => 'N/A', // Not used for pickup-only requests
                'dropoff_date' => now()->toDateString(), // Default to today
                'time_option' => 'anytime', // Default for pickup-only
                'start_time' => null,
                'end_time' => null,
                'address' => 'Establishment Address', // Will be set to establishment's address when fulfilled
                'pickup_method' => 'pickup', // Always pickup
                'contact_name' => $validated['contactName'],
                'phone_number' => $validated['phoneNumber'],
                'email' => $validated['email'],
                'status' => 'pending',
                'matches' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request published successfully!',
                'data' => [
                    'id' => $donationRequest->donation_request_id,
                    'foodType' => $donationRequest->item_name,
                    'quantity' => $donationRequest->quantity,
                    'matches' => $donationRequest->matches,
                    'status' => $donationRequest->status,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish request. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific donation request (for foodbank's own requests)
     */
    public function showDonationRequest($id)
    {
        if (session('user_type') !== 'foodbank') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as a foodbank.'
            ], 403);
        }

        $foodbankId = session('user_id');

        try {
            $donationRequest = DonationRequest::where('donation_request_id', $id)
                ->where('foodbank_id', $foodbankId)
                ->whereNull('establishment_id')
                ->with(['donation', 'fulfilledBy'])
                ->first();

            if (!$donationRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation request not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => DonationRequestService::formatRequestData($donationRequest)
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching donation request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch donation request details.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.'
            ], 500);
        }
    }

    /**
     * Update a donation request (for foodbank's own requests)
     */
    public function updateDonationRequest(Request $request, $id)
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as a foodbank.'
            ], 403);
        }

        // Check verification status
        if (!$this->checkFoodbankVerification()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not verified. Please wait for admin approval.',
                'error' => 'unverified_account'
            ], 403);
        }

        $foodbankId = session('user_id');

        // Validate the request
        $validated = $request->validate([
            'itemName' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'contactName' => 'required|string|max:255',
            'phoneNumber' => 'required|string',
            'email' => 'required|email|max:255',
            'status' => 'nullable|in:pending,active,completed,expired',
        ]);

        try {
            $donationRequest = DonationRequest::where('donation_request_id', $id)
                ->where('foodbank_id', $foodbankId)
                ->whereNull('establishment_id') // Only foodbank's own requests
                ->first();

            if (!$donationRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation request not found.'
                ], 404);
            }

            // Update the donation request (pickup only - delivery fields removed)
            $donationRequest->update([
                'item_name' => $validated['itemName'],
                'quantity' => $validated['quantity'],
                'category' => $validated['category'],
                'description' => $validated['description'] ?? null,
                // Delivery fields not updated - pickup only
                'pickup_method' => 'pickup', // Always pickup
                'contact_name' => $validated['contactName'],
                'phone_number' => $validated['phoneNumber'],
                'email' => $validated['email'],
                'status' => $validated['status'] ?? $donationRequest->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request updated successfully!',
                'data' => [
                    'id' => $donationRequest->donation_request_id,
                    'foodType' => $donationRequest->item_name,
                    'quantity' => $donationRequest->quantity,
                    'status' => $donationRequest->status,
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating donation request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update request. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.'
            ], 500);
        }
    }

    /**
     * Delete a donation request (for foodbank's own requests)
     */
    public function deleteDonationRequest($id)
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as a foodbank.'
            ], 403);
        }

        // Check verification status
        if (!$this->checkFoodbankVerification()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not verified. Please wait for admin approval.',
                'error' => 'unverified_account'
            ], 403);
        }

        $foodbankId = session('user_id');

        try {
            $donationRequest = DonationRequest::where('donation_request_id', $id)
                ->where('foodbank_id', $foodbankId)
                ->whereNull('establishment_id') // Only foodbank's own requests
                ->first();

            if (!$donationRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation request not found.'
                ], 404);
            }

            // Check if request has been fulfilled (has donation_id)
            if ($donationRequest->donation_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete a request that has been fulfilled.'
                ], 400);
            }

            // Delete the donation request
            $donationRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Request deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error deleting donation request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete request. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.'
            ], 500);
        }
    }

    /**
     * Confirm pickup for foodbank's own donation request
     */
    public function confirmFoodbankRequestPickup(Request $request, $requestId)
    {
        if (session('user_type') !== 'foodbank') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as a foodbank.'
            ], 403);
        }

        $foodbankId = session('user_id');

        try {
            $donationRequest = DonationRequest::where('donation_request_id', $requestId)
                ->where('foodbank_id', $foodbankId)
                ->whereNull('establishment_id')
                ->whereIn('status', [
                    DonationRequestService::STATUS_PENDING,
                    DonationRequestService::STATUS_ACCEPTED,
                    DonationRequestService::STATUS_PENDING_CONFIRMATION
                ])
                ->first();

            if (!$donationRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation request not found or cannot confirm pickup.'
                ], 404);
            }

            $donationRequest->status = DonationRequestService::STATUS_COMPLETED;
            $donationRequest->fulfilled_at = now();
            $donationRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Pickup confirmed successfully!',
                'data' => [
                    'id' => $donationRequest->donation_request_id,
                    'status' => $donationRequest->status,
                    'fulfilled_at' => $donationRequest->fulfilled_at->format('F d, Y g:i A'),
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error confirming pickup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm pickup. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.'
            ], 500);
        }
    }

    /**
     * Confirm delivery for foodbank's own donation request (removed - pickup only)
     * Redirects to pickup confirmation
     */
    public function confirmFoodbankRequestDelivery(Request $request, $requestId)
    {
        // Delivery is no longer supported - redirect to pickup confirmation
        return $this->confirmFoodbankRequestPickup($request, $requestId);
    }

    /**
     * Accept a donation from an establishment
     */
    public function acceptDonation(Request $request, $donationId)
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as a foodbank.'
            ], 403);
        }

        $foodbankId = session('user_id');

        try {
            $donation = Donation::where('donation_id', $donationId)
                ->where('foodbank_id', $foodbankId)
                ->with(['establishment'])
                ->first();

            if (!$donation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation not found.'
                ], 404);
            }

            // Check if a DonationRequest already exists for this donation
            $existingRequest = DonationRequest::where('donation_id', $donationId)
                ->where('foodbank_id', $foodbankId)
                ->whereNotNull('establishment_id')
                ->first();

            if ($existingRequest) {
                // Update existing request to accepted
                $existingRequest->status = 'accepted';
                $existingRequest->save();
            } else {
                // Create a new DonationRequest from the accepted donation
                // Handle scheduled_time format - convert to time string if it's a datetime
                $scheduledTime = null;
                if ($donation->scheduled_time) {
                    try {
                        if (is_string($donation->scheduled_time)) {
                            // Extract time part if it's a datetime string
                            if (strpos($donation->scheduled_time, ' ') !== false) {
                                $parts = explode(' ', $donation->scheduled_time);
                                $scheduledTime = end($parts);
                            } else {
                                $scheduledTime = $donation->scheduled_time;
                            }
                        } elseif ($donation->scheduled_time instanceof \DateTime || $donation->scheduled_time instanceof \Carbon\Carbon) {
                            $scheduledTime = $donation->scheduled_time->format('H:i:s');
                        }
                    } catch (\Exception $e) {
                        // If time parsing fails, set to null
                        $scheduledTime = null;
                    }
                }
                
                // Ensure scheduled_date is properly formatted
                $scheduledDate = null;
                if ($donation->scheduled_date) {
                    try {
                        if ($donation->scheduled_date instanceof \DateTime || $donation->scheduled_date instanceof \Carbon\Carbon) {
                            $scheduledDate = $donation->scheduled_date->format('Y-m-d');
                        } elseif (is_string($donation->scheduled_date)) {
                            // Validate it's a valid date string
                            $scheduledDate = date('Y-m-d', strtotime($donation->scheduled_date));
                        }
                    } catch (\Exception $e) {
                        // If date parsing fails, use tomorrow
                        $scheduledDate = now()->addDay()->format('Y-m-d');
                    }
                } else {
                    // Default to tomorrow if no date
                    $scheduledDate = now()->addDay()->format('Y-m-d');
                }
                
                // Get foodbank details for required fields
                $foodbank = Foodbank::where('foodbank_id', $foodbankId)->first();
                
                // Prepare data for DonationRequest creation
                // Note: Some fields are required by the original schema but not in Donation records
                $requestData = [
                    'foodbank_id' => $foodbankId,
                    'establishment_id' => $donation->establishment_id,
                    'donation_id' => $donation->donation_id,
                    'item_name' => $donation->item_name ?? 'Donation Item',
                    'quantity' => $donation->quantity ?? 1,
                    'unit' => $donation->unit ?? 'pcs',
                    'category' => $donation->item_category ?? 'other',
                    'description' => $donation->description,
                    'expiry_date' => $donation->expiry_date,
                    'scheduled_date' => $scheduledDate,
                    'scheduled_time' => $scheduledTime,
                    'pickup_method' => 'pickup',
                    'establishment_notes' => $donation->establishment_notes,
                    'status' => DonationRequestService::STATUS_ACCEPTED,
                    // Required fields from original schema (not in Donation model)
                    'distribution_zone' => 'General', // Default zone
                    'dropoff_date' => $scheduledDate, // Use scheduled date as dropoff date
                    'address' => $foodbank->address ?? 'Address not provided',
                    'contact_name' => $foodbank->contact_person ?? $foodbank->organization_name ?? 'Foodbank Contact',
                    'phone_number' => $foodbank->phone_no ?? 'Not provided',
                    'email' => $foodbank->email ?? 'notprovided@example.com',
                    'time_option' => 'anytime',
                ];
                
                $donationRequest = DonationRequest::create($requestData);
            }

            // Update donation status to ready_for_collection
            $donation->status = 'ready_for_collection';
            $donation->save();
            
            // Reload donation with relationships for notification
            $donation->load(['foodbank', 'establishment']);
            
            // Send notification to establishment
            NotificationService::notifyDonationApproved($donation);
            
            // Send notification to foodbank (confirmation of their action)
            $establishmentName = $donation->establishment ? $donation->establishment->business_name : 'an establishment';
            $establishmentAddress = $donation->establishment ? ($donation->establishment->address ?? 'Address not provided') : 'Address not provided';
            Notification::createNotification(
                'foodbank',
                $foodbankId,
                'donation_accepted',
                'Donation Accepted',
                "You have accepted the donation offer for {$donation->item_name} from {$establishmentName}. Pickup location: {$establishmentAddress}. Please confirm pickup when completed.",
                [
                    'donation_id' => $donation->donation_id,
                    'data' => [
                        'item_name' => $donation->item_name,
                        'quantity' => $donation->quantity,
                        'establishment_name' => $establishmentName,
                        'pickup_method' => 'pickup',
                    ]
                ]
            );

            // Dispatch event for automatic logging
            \App\Events\DonationOfferAccepted::dispatch($donation, $donationRequest, $foodbankId, 'foodbank');

            return response()->json([
                'success' => true,
                'message' => 'Donation accepted successfully! It has been moved to the Accepted section.',
                'data' => [
                    'id' => $donation->donation_id,
                    'status' => $donation->status,
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error accepting donation: ' . $e->getMessage(), [
                'donation_id' => $donationId,
                'foodbank_id' => $foodbankId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept donation. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred. Please contact support.'
            ], 500);
        }
    }

    /**
     * Get establishment contact details
     */
    public function getEstablishmentContact($establishmentId)
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as a foodbank.'
            ], 403);
        }

        try {
            $establishment = Establishment::where('establishment_id', $establishmentId)->first();

            if (!$establishment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Establishment not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $establishment->establishment_id,
                    'business_name' => $establishment->business_name,
                    'email' => $establishment->email,
                    'phone_no' => $establishment->phone_no ?? 'Not provided',
                    'address' => $establishment->address ?? 'Not provided',
                    'owner_name' => $establishment->owner_name ?? 'Not provided',
                    'business_type' => $establishment->business_type ?? 'Not provided',
                    'is_verified' => $establishment->is_verified ?? false,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve establishment details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept a donation request from an establishment
     */
    public function acceptDonationRequest(Request $request, $requestId)
    {
        if (session('user_type') !== 'foodbank') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as a foodbank.'
            ], 403);
        }

        // Check verification status
        if (!$this->checkFoodbankVerification()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not verified. Please wait for admin approval.',
                'error' => 'unverified_account'
            ], 403);
        }

        $foodbankId = session('user_id');

        try {
            $donationRequest = DonationRequest::where('donation_request_id', $requestId)
                ->where('foodbank_id', $foodbankId)
                ->whereNotNull('establishment_id')
                ->whereIn('status', [
                    DonationRequestService::STATUS_PENDING,
                    DonationRequestService::STATUS_PENDING_CONFIRMATION
                ])
                ->first();

            if (!$donationRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation request not found or already processed.'
                ], 404);
            }

            if (!DonationRequestService::acceptRequest($donationRequest)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot accept this request. Invalid status transition.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Donation request accepted successfully!',
                'data' => [
                    'id' => $donationRequest->donation_request_id,
                    'status' => $donationRequest->status,
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error accepting donation request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept donation request. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.'
            ], 500);
        }
    }

    /**
     * Decline a donation request from an establishment
     */
    public function declineDonationRequest(Request $request, $requestId)
    {
        if (session('user_type') !== 'foodbank') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as a foodbank.'
            ], 403);
        }

        // Check verification status
        if (!$this->checkFoodbankVerification()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not verified. Please wait for admin approval.',
                'error' => 'unverified_account'
            ], 403);
        }

        $foodbankId = session('user_id');

        try {
            $donationRequest = DonationRequest::where('donation_request_id', $requestId)
                ->where('foodbank_id', $foodbankId)
                ->whereNotNull('establishment_id')
                ->whereIn('status', [
                    DonationRequestService::STATUS_PENDING,
                    DonationRequestService::STATUS_PENDING_CONFIRMATION
                ])
                ->first();

            if (!$donationRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation request not found or already processed.'
                ], 404);
            }

            if (!DonationRequestService::declineRequest($donationRequest)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot decline this request. Invalid status transition.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Donation request declined successfully.',
                'data' => [
                    'id' => $donationRequest->donation_request_id,
                    'status' => $donationRequest->status,
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error declining donation request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to decline donation request. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.'
            ], 500);
        }
    }

    /**
     * Confirm pickup for an accepted donation request (from establishment)
     */
    public function confirmPickup(Request $request, $requestId)
    {
        if (session('user_type') !== 'foodbank') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as a foodbank.'
            ], 403);
        }

        $foodbankId = session('user_id');

        try {
            $donationRequest = DonationRequest::where('donation_request_id', $requestId)
                ->where('foodbank_id', $foodbankId)
                ->whereNotNull('establishment_id')
                ->whereIn('status', [DonationRequestService::STATUS_ACCEPTED, DonationRequestService::STATUS_PENDING_CONFIRMATION])
                ->first();

            if (!$donationRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation request not found or cannot confirm pickup.'
                ], 404);
            }

            $donation = DonationRequestService::confirmCompletion($donationRequest, 'pickup');

            if (!$donation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot confirm pickup. Invalid status transition.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pickup confirmed successfully! Donation request marked as completed.',
                'data' => [
                    'id' => $donationRequest->donation_request_id,
                    'status' => $donationRequest->status,
                    'donation_id' => $donation->donation_id,
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error confirming pickup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm pickup. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.'
            ], 500);
        }
    }

    /**
     * Confirm delivery for an accepted donation request (removed - pickup only)
     * Redirects to pickup confirmation
     */
    public function confirmDelivery(Request $request, $requestId)
    {
        // Delivery is no longer supported - redirect to pickup confirmation
        return $this->confirmPickup($request, $requestId);
    }


    /**
     * Decline a donation from an establishment
     */
    public function declineDonation(Request $request, $donationId)
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as a foodbank.'
            ], 403);
        }

        $foodbankId = session('user_id');

        try {
            $donation = Donation::where('donation_id', $donationId)
                ->where('foodbank_id', $foodbankId)
                ->with(['establishment'])
                ->first();

            if (!$donation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation not found.'
                ], 404);
            }

            // Update donation status to cancelled
            $donation->status = 'cancelled';
            $donation->save();

            // Check if a DonationRequest already exists for this donation
            $donationRequest = DonationRequest::where('donation_id', $donationId)
                ->where('foodbank_id', $foodbankId)
                ->whereNotNull('establishment_id')
                ->first();

            $foodbank = Foodbank::find($foodbankId);

            if ($donationRequest) {
                // Update existing request to declined
                $donationRequest->status = 'declined';
                $donationRequest->save();
            } else {
                // Create a new DonationRequest with declined status
                $scheduledDate = $donation->scheduled_date ? $donation->scheduled_date->format('Y-m-d') : now()->format('Y-m-d');
                $scheduledTime = $donation->scheduled_time ? (is_string($donation->scheduled_time) ? $donation->scheduled_time : $donation->scheduled_time->format('H:i:s')) : null;

                $donationRequest = DonationRequest::create([
                    'foodbank_id' => $foodbankId,
                    'establishment_id' => $donation->establishment_id,
                    'donation_id' => $donation->donation_id,
                    'item_name' => $donation->item_name,
                    'quantity' => $donation->quantity,
                    'unit' => $donation->unit ?? 'pcs',
                    'category' => $donation->item_category ?? 'other',
                    'description' => $donation->description,
                    'expiry_date' => $donation->expiry_date,
                    'scheduled_date' => $scheduledDate,
                    'scheduled_time' => $scheduledTime,
                    'pickup_method' => 'pickup', // Always pickup
                    'establishment_notes' => $donation->establishment_notes,
                    'status' => 'declined',
                    // Default values for required fields in donation_requests table
                    'distribution_zone' => $foodbank->distribution_zone ?? 'N/A',
                    'dropoff_date' => $scheduledDate,
                    'address' => $foodbank->address ?? 'N/A',
                    'contact_name' => $foodbank->contact_person ?? 'N/A',
                    'phone_number' => $foodbank->phone_no ?? 'N/A',
                    'email' => $foodbank->email ?? 'N/A',
                ]);
            }

            // Send notification to establishment
            if ($donation->establishment_id) {
                Notification::createNotification(
                    'establishment',
                    $donation->establishment_id,
                    'donation_declined',
                    'Donation Declined',
                    "Your donation offer for {$donation->item_name} has been declined by the foodbank.",
                    [
                        'donation_id' => $donation->donation_id,
                        'donation_request_id' => $donationRequest->donation_request_id ?? null,
                        'data' => [
                            'item_name' => $donation->item_name,
                            'quantity' => $donation->quantity,
                        ]
                    ]
                );
            }

            // Dispatch event for automatic logging
            \App\Events\DonationOfferDeclined::dispatch($donation, $donationRequest, $foodbankId, 'foodbank');

            return response()->json([
                'success' => true,
                'message' => 'Donation declined successfully.',
                'data' => [
                    'id' => $donation->donation_id,
                    'status' => $donation->status,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to decline donation. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show partner network page for foodbank
     */
    public function partnerNetwork()
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return redirect()->route('login')->with('error', 'Access denied. Please login as a foodbank.');
        }
        
        $user = $this->getUserData();
        $foodbankId = session('user_id');
        $foodbank = Foodbank::find($foodbankId);
        $isVerified = $foodbank && $foodbank->isVerified();
        
        // Get establishments that have completed donations (status = 'collected') with this foodbank
        // This is used for the "Business Partnered" stat count
        $partneredEstablishmentIds = Donation::where('foodbank_id', $foodbankId)
            ->where('status', 'collected')
            ->distinct()
            ->pluck('establishment_id')
            ->toArray();
        
        // Fetch ALL establishments from database (for display in Food Businesses section)
        $establishments = Establishment::all();
        
        // Map establishments to partner format expected by frontend
        $partners = $establishments->map(function ($establishment) use ($foodbankId) {
            // Calculate average rating from reviews (if available)
            $reviews = Review::where('establishment_id', $establishment->establishment_id)->get();
            $rating = $reviews->count() > 0 
                ? round($reviews->avg('rating'), 1) 
                : 0; // 0 if no reviews
            
            // Count completed donations with this foodbank (0 if no donations)
            $donations = Donation::where('establishment_id', $establishment->establishment_id)
                ->where('foodbank_id', $foodbankId)
                ->where('status', 'collected')
                ->count();
            
            // Calculate impact (meals provided) - estimate based on donations
            // Can be updated to use actual meal count when available
            $impact = $donations * 3; // Estimate: 3 meals per donation
            
            // Normalize business type to lowercase for filtering
            $businessType = strtolower($establishment->business_type ?? 'other');
            
            return [
                'id' => $establishment->establishment_id,
                'name' => $establishment->business_name,
                'type' => $businessType,
                'location' => $establishment->address ?? 'Address not provided',
                'rating' => $rating,
                'donations' => $donations,
                'impact' => $impact,
                'profile_image' => $establishment->profile_image 
                    ? asset('storage/' . $establishment->profile_image) 
                    : null,
                // Additional fields for modal details
                'email' => $establishment->email,
                'phone' => $establishment->phone_no,
                'owner' => $establishment->owner_fname . ' ' . $establishment->owner_lname,
                'registered_at' => $establishment->registered_at ? $establishment->registered_at->format('F Y') : 'N/A',
            ];
        })->toArray();
        
        // Calculate stats - count of establishments with completed donations (for Business Partnered stat)
        $totalPartners = count($partneredEstablishmentIds);
        
        return view('foodbank.partner-network', compact('user', 'partners', 'totalPartners'));
    }

    /**
     * Show donation history page for foodbank
     */
    public function donationHistory(Request $request)
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return redirect()->route('login')->with('error', 'Access denied. Please login as a foodbank.');
        }
        
        $user = $this->getUserData();
        $foodbankId = session('user_id');
        $foodbank = Foodbank::find($foodbankId);
        $isVerified = $foodbank && $foodbank->isVerified();
        
        // Get Donation records (fulfilled donations)
        $donationQuery = Donation::where('foodbank_id', $foodbankId)
            ->with(['establishment', 'donationRequest']);
        
        // Get DonationRequest records (accepted, declined, completed requests)
        $requestQuery = DonationRequest::where('foodbank_id', $foodbankId)
            ->whereNotNull('establishment_id')
            ->whereIn('status', [
                DonationRequestService::STATUS_ACCEPTED,
                DonationRequestService::STATUS_DECLINED,
                DonationRequestService::STATUS_COMPLETED
            ])
            ->with(['establishment', 'donation']);
        
        // Apply filters to donations
        if ($request->filled('status')) {
            $status = $request->status;
            // Check if it's a donation status or request status
            $donationStatuses = ['pending_pickup', 'ready_for_collection', 'collected', 'cancelled', 'expired'];
            $requestStatuses = ['accepted', 'declined', 'completed'];
            
            if (in_array($status, $donationStatuses)) {
                $donationQuery->where('status', $status);
            } elseif (in_array($status, $requestStatuses)) {
                $requestQuery->where('status', $status);
            }
        }
        
        if ($request->filled('category')) {
            $donationQuery->where('item_category', $request->category);
            $requestQuery->where('category', $request->category);
        }
        
        if ($request->filled('establishment_id')) {
            $donationQuery->where('establishment_id', $request->establishment_id);
            $requestQuery->where('establishment_id', $request->establishment_id);
        }
        
        if ($request->filled('date_from')) {
            $donationQuery->where('scheduled_date', '>=', $request->date_from);
            $requestQuery->where('scheduled_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $donationQuery->where('scheduled_date', '<=', $request->date_to);
            $requestQuery->where('scheduled_date', '<=', $request->date_to);
        }
        
        // Get all donations and requests for filtering options
        $allDonations = Donation::where('foodbank_id', $foodbankId)->get();
        $allRequests = DonationRequest::where('foodbank_id', $foodbankId)
            ->whereNotNull('establishment_id')
            ->whereIn('status', [
                DonationRequestService::STATUS_ACCEPTED,
                DonationRequestService::STATUS_DECLINED,
                DonationRequestService::STATUS_COMPLETED
            ])
            ->get();
        
        // Get unique categories from both donations and requests
        $donationCategories = $allDonations->pluck('item_category')->unique();
        $requestCategories = $allRequests->pluck('category')->unique();
        $categories = $donationCategories->merge($requestCategories)->unique()->sort()->values();
        
        // Get unique establishments from both donations and requests
        $donationEstablishmentIds = $allDonations->pluck('establishment_id')->unique();
        $requestEstablishmentIds = $allRequests->pluck('establishment_id')->unique();
        $allEstablishmentIds = $donationEstablishmentIds->merge($requestEstablishmentIds)->unique();
        $establishments = Establishment::whereIn('establishment_id', $allEstablishmentIds)
            ->get(['establishment_id', 'business_name'])
            ->map(function ($est) {
                return ['id' => $est->establishment_id, 'name' => $est->business_name];
            });
        
        // Format Donation records (only if not filtering by request status)
        $formattedDonations = collect();
        if (!$request->filled('status') || in_array($request->status, ['pending_pickup', 'ready_for_collection', 'collected', 'cancelled', 'expired'])) {
            $formattedDonations = $donationQuery->get()->map(function ($donation) {
            $establishment = $donation->establishment;
            
            // Format scheduled time
            $timeDisplay = 'N/A';
            if ($donation->scheduled_time) {
                $timeDisplay = is_string($donation->scheduled_time) 
                    ? substr($donation->scheduled_time, 0, 5) 
                    : $donation->scheduled_time->format('H:i');
            }
            
            return [
                'id' => $donation->donation_id,
                'type' => 'donation',
                'donation_number' => $donation->donation_number,
                'establishment_name' => $establishment->business_name ?? 'Unknown',
                'establishment_id' => $donation->establishment_id,
                'item_name' => $donation->item_name,
                'category' => $donation->item_category,
                'quantity' => $donation->quantity,
                'unit' => $donation->unit,
                'description' => $donation->description,
                'expiry_date' => $donation->expiry_date ? $donation->expiry_date->format('Y-m-d') : null,
                'expiry_date_display' => $donation->expiry_date ? $donation->expiry_date->format('F d, Y') : 'N/A',
                'status' => $donation->status,
                'status_display' => ucfirst(str_replace('_', ' ', $donation->status)),
                'pickup_method' => $donation->pickup_method,
                'pickup_method_display' => ucfirst($donation->pickup_method),
                'scheduled_date' => $donation->scheduled_date->format('Y-m-d'),
                'scheduled_date_display' => $donation->scheduled_date->format('F d, Y'),
                'scheduled_time' => $timeDisplay,
                'created_at' => $donation->created_at->format('Y-m-d H:i:s'),
                'created_at_display' => $donation->created_at->format('F d, Y g:i A'),
                'collected_at' => $donation->collected_at ? $donation->collected_at->format('Y-m-d H:i:s') : null,
                'collected_at_display' => $donation->collected_at ? $donation->collected_at->format('F d, Y g:i A') : 'N/A',
                'handler_name' => $donation->handler_name ?? 'N/A',
                'establishment_notes' => $donation->establishment_notes,
                'foodbank_notes' => $donation->foodbank_notes,
                'is_urgent' => $donation->is_urgent,
                'is_nearing_expiry' => $donation->is_nearing_expiry,
                'sort_date' => $donation->collected_at ?? $donation->created_at,
            ];
            });
        }
        
        // Format DonationRequest records (only if not filtering by donation status)
        $formattedRequests = collect();
        if (!$request->filled('status') || in_array($request->status, ['accepted', 'declined', 'completed'])) {
            $formattedRequests = $requestQuery->get()->map(function ($donationRequest) {
                $establishment = $donationRequest->establishment;
                $formatted = DonationRequestService::formatRequestData($donationRequest);
                
                // Format scheduled date
                $scheduledDate = $donationRequest->scheduled_date ?? $donationRequest->dropoff_date ?? $donationRequest->created_at;
                $scheduledDateDisplay = $scheduledDate instanceof \Carbon\Carbon 
                    ? $scheduledDate->format('F d, Y') 
                    : ($scheduledDate ? \Carbon\Carbon::parse($scheduledDate)->format('F d, Y') : 'N/A');
                
                return [
                    'id' => $donationRequest->donation_request_id,
                    'type' => 'request',
                    'donation_number' => $donationRequest->donation ? $donationRequest->donation->donation_number : 'REQ-' . substr($donationRequest->donation_request_id, 0, 8),
                    'establishment_name' => $establishment->business_name ?? 'Unknown',
                    'establishment_id' => $donationRequest->establishment_id,
                    'item_name' => $donationRequest->item_name,
                    'category' => $donationRequest->category,
                    'quantity' => $donationRequest->quantity,
                    'unit' => $donationRequest->unit ?? 'pcs',
                    'description' => $donationRequest->description,
                    'expiry_date' => $donationRequest->expiry_date ? $donationRequest->expiry_date->format('Y-m-d') : null,
                    'expiry_date_display' => $donationRequest->expiry_date ? $donationRequest->expiry_date->format('F d, Y') : 'N/A',
                    'status' => $donationRequest->status,
                    'status_display' => DonationRequestService::getStatusDisplay($donationRequest->status),
                    'pickup_method' => 'pickup',
                    'pickup_method_display' => 'Pickup',
                    'scheduled_date' => $scheduledDate instanceof \Carbon\Carbon ? $scheduledDate->format('Y-m-d') : ($scheduledDate ? \Carbon\Carbon::parse($scheduledDate)->format('Y-m-d') : null),
                    'scheduled_date_display' => $scheduledDateDisplay,
                    'scheduled_time' => $formatted['scheduled_time_display'] ?? 'N/A',
                    'created_at' => $donationRequest->created_at->format('Y-m-d H:i:s'),
                    'created_at_display' => $donationRequest->created_at->format('F d, Y g:i A'),
                    'collected_at' => null,
                    'collected_at_display' => 'N/A',
                    'handler_name' => 'N/A',
                    'establishment_notes' => $donationRequest->establishment_notes,
                    'foodbank_notes' => null,
                    'is_urgent' => false,
                    'is_nearing_expiry' => false,
                    'sort_date' => $donationRequest->fulfilled_at ?? $donationRequest->updated_at ?? $donationRequest->created_at,
                ];
            });
        }
        
        // Merge and sort by date (most recent first)
        $allRecords = $formattedDonations->merge($formattedRequests)
            ->sortByDesc(function ($record) {
                return $record['sort_date'] instanceof \Carbon\Carbon 
                    ? $record['sort_date']->timestamp 
                    : \Carbon\Carbon::parse($record['sort_date'])->timestamp;
            })
            ->values();
        
        // Create a custom paginator
        $currentPage = $request->get('page', 1);
        $perPage = 10;
        $currentItems = $allRecords->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $donations = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $allRecords->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Calculate statistics (include both donations and requests)
        $totalQuantity = $allDonations->sum('quantity') + $allRequests->sum('quantity');
        $stats = [
            'total_donations' => $allDonations->count() + $allRequests->count(),
            'total_quantity' => $totalQuantity,
            'establishment_participation' => $allEstablishmentIds->count(),
            'by_status' => [
                'pending_pickup' => $allDonations->where('status', 'pending_pickup')->count(),
                'ready_for_collection' => $allDonations->where('status', 'ready_for_collection')->count(),
                'collected' => $allDonations->where('status', 'collected')->count(),
                'cancelled' => $allDonations->where('status', 'cancelled')->count(),
                'expired' => $allDonations->where('status', 'expired')->count(),
                'accepted' => $allRequests->where('status', DonationRequestService::STATUS_ACCEPTED)->count(),
                'declined' => $allRequests->where('status', DonationRequestService::STATUS_DECLINED)->count(),
                'completed' => $allRequests->where('status', DonationRequestService::STATUS_COMPLETED)->count(),
            ],
        ];
        
        return view('foodbank.donation-history', compact(
            'user', 
            'donations', 
            'categories', 
            'establishments', 
            'stats'
        ));
    }

    /**
     * Export donation history reports
     */
    public function exportDonationHistory(Request $request)
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $foodbankId = session('user_id');
        $exportType = $request->get('type', 'history');
        $format = $request->get('format', 'csv'); // csv, excel, pdf
        
        // Handle format-specific exports for history type
        if ($exportType === 'history' && in_array($format, ['excel', 'pdf'])) {
            return $this->exportFoodbankDonationHistoryFormatted($request, $format);
        }
        
        $filename = '';
        $headers = [];
        $data = [];
        
        switch ($exportType) {
            case 'history':
                $filename = 'donation_history_' . date('Y-m-d_His') . '.csv';
                $headers = [
                    'Donation ID',
                    'Donation Number',
                    'Establishment',
                    'Item Name',
                    'Category',
                    'Quantity',
                    'Unit',
                    'Status',
                    'Scheduled Date',
                    'Scheduled Time',
                    'Pickup Method',
                    'Expiry Date',
                    'Created At',
                    'Collected At',
                    'Handler',
                    'Is Urgent',
                    'Is Nearing Expiry',
                    'Description',
                    'Establishment Notes',
                    'Foodbank Notes'
                ];
                
                // Build queries with same filters as donationHistory - include both Donation and DonationRequest
                $donationQuery = Donation::where('foodbank_id', $foodbankId)
                    ->with(['establishment']);
                
                $requestQuery = DonationRequest::where('foodbank_id', $foodbankId)
                    ->whereNotNull('establishment_id') // Only establishment-submitted requests
                    ->whereIn('status', [
                        DonationRequestService::STATUS_ACCEPTED,
                        DonationRequestService::STATUS_DECLINED,
                        DonationRequestService::STATUS_COMPLETED
                    ])
                    ->with(['establishment', 'donation']);
                
                // Apply filters if provided
                if ($request->filled('status')) {
                    $status = $request->status;
                    $donationStatuses = ['pending_pickup', 'ready_for_collection', 'collected', 'cancelled', 'expired'];
                    $requestStatuses = ['accepted', 'declined', 'completed'];
                    
                    if (in_array($status, $donationStatuses)) {
                        $donationQuery->where('status', $status);
                    } elseif (in_array($status, $requestStatuses)) {
                        $requestQuery->where('status', $status);
                    }
                }
                
                if ($request->filled('category')) {
                    $donationQuery->where('item_category', $request->category);
                    $requestQuery->where('category', $request->category);
                }
                
                if ($request->filled('establishment_id')) {
                    $donationQuery->where('establishment_id', $request->establishment_id);
                    $requestQuery->where('establishment_id', $request->establishment_id);
                }
                
                if ($request->filled('date_from')) {
                    $donationQuery->where('created_at', '>=', $request->date_from);
                    $requestQuery->where('created_at', '>=', $request->date_from);
                }
                
                if ($request->filled('date_to')) {
                    $donationQuery->where('created_at', '<=', $request->date_to . ' 23:59:59');
                    $requestQuery->where('created_at', '<=', $request->date_to . ' 23:59:59');
                }
                
                $allDonations = $donationQuery->orderBy('created_at', 'desc')->get();
                $allRequests = $requestQuery->orderBy('created_at', 'desc')->get();
                
                // Format and add Donation records
                foreach ($allDonations as $donation) {
                    $data[] = [
                        $donation->donation_id,
                        $donation->donation_number,
                        $donation->establishment->business_name ?? 'Unknown',
                        $donation->item_name,
                        $donation->item_category,
                        $donation->quantity,
                        $donation->unit,
                        ucfirst(str_replace('_', ' ', $donation->status)),
                        $donation->scheduled_date ? $donation->scheduled_date->format('Y-m-d') : 'N/A',
                        $donation->scheduled_time ? (is_string($donation->scheduled_time) ? substr($donation->scheduled_time, 0, 5) : $donation->scheduled_time->format('H:i')) : 'N/A',
                        ucfirst($donation->pickup_method),
                        $donation->expiry_date ? $donation->expiry_date->format('Y-m-d') : 'N/A',
                        $donation->created_at->format('Y-m-d H:i:s'),
                        $donation->collected_at ? $donation->collected_at->format('Y-m-d H:i:s') : 'N/A',
                        $donation->handler_name ?? 'N/A',
                        $donation->is_urgent ? 'Yes' : 'No',
                        $donation->is_nearing_expiry ? 'Yes' : 'No',
                        $donation->description ?? '',
                        $donation->establishment_notes ?? '',
                        $donation->foodbank_notes ?? ''
                    ];
                }
                
                // Format and add DonationRequest records
                foreach ($allRequests as $donationRequest) {
                    $establishment = $donationRequest->establishment;
                    $formatted = DonationRequestService::formatRequestData($donationRequest);
                    
                    $scheduledDate = $donationRequest->scheduled_date ?? $donationRequest->dropoff_date ?? $donationRequest->created_at;
                    $scheduledDateFormatted = $scheduledDate instanceof \Carbon\Carbon 
                        ? $scheduledDate->format('Y-m-d') 
                        : ($scheduledDate ? \Carbon\Carbon::parse($scheduledDate)->format('Y-m-d') : 'N/A');
                    
                    // Format scheduled time
                    $scheduledTimeFormatted = $formatted['time_display'] ?? 'N/A';
                    if ($scheduledTimeFormatted === 'N/A' && $formatted['scheduled_time_display'] && $formatted['scheduled_time_display'] !== 'N/A') {
                        $scheduledTimeFormatted = $formatted['scheduled_time_display'];
                    }
                    
                    $data[] = [
                        $donationRequest->donation_request_id,
                        $donationRequest->donation ? $donationRequest->donation->donation_number : 'REQ-' . substr($donationRequest->donation_request_id, 0, 8),
                        $establishment->business_name ?? 'Unknown',
                        $donationRequest->item_name,
                        $donationRequest->category,
                        $donationRequest->quantity,
                        $donationRequest->unit ?? 'pcs',
                        DonationRequestService::getStatusDisplay($donationRequest->status),
                        $scheduledDateFormatted,
                        $scheduledTimeFormatted,
                        'Pickup',
                        $donationRequest->expiry_date ? $donationRequest->expiry_date->format('Y-m-d') : 'N/A',
                        $donationRequest->created_at->format('Y-m-d H:i:s'),
                        $donationRequest->fulfilled_at ? $donationRequest->fulfilled_at->format('Y-m-d H:i:s') : 'N/A',
                        'N/A',
                        'No',
                        'No',
                        $donationRequest->description ?? '',
                        $donationRequest->establishment_notes ?? '',
                        ''
                    ];
                }
                break;
                
            case 'monthly':
                $month = $request->get('month', date('Y-m'));
                $filename = 'monthly_report_' . $month . '.csv';
                
                $startDate = Carbon::parse($month)->startOfMonth();
                $endDate = Carbon::parse($month)->endOfMonth();
                
                $donations = Donation::where('foodbank_id', $foodbankId)
                    ->whereBetween('scheduled_date', [$startDate, $endDate])
                    ->with(['establishment'])
                    ->get();
                
                // Summary statistics
                $totalDonations = $donations->count();
                $totalQuantity = $donations->sum('quantity');
                $byStatus = $donations->groupBy('status')->map->count();
                $byCategory = $donations->groupBy('item_category')->map->count();
                $byEstablishment = $donations->groupBy('establishment_id')->map->count();
                
                $headers = [
                    'Metric',
                    'Value'
                ];
                
                $data = [
                    ['Month', $month],
                    ['Total Donations', $totalDonations],
                    ['Total Quantity', $totalQuantity],
                    ['', ''],
                    ['Status Breakdown', ''],
                ];
                
                foreach ($byStatus as $status => $count) {
                    $data[] = [ucfirst(str_replace('_', ' ', $status)), $count];
                }
                
                $data[] = ['', ''];
                $data[] = ['Category Breakdown', ''];
                
                foreach ($byCategory as $category => $count) {
                    $data[] = [ucfirst($category), $count];
                }
                
                $data[] = ['', ''];
                $data[] = ['Establishment Participation', $byEstablishment->count()];
                $data[] = ['', ''];
                $data[] = ['Detailed Donations', ''];
                $data[] = [
                    'Donation Number',
                    'Establishment',
                    'Item Name',
                    'Category',
                    'Quantity',
                    'Status',
                    'Scheduled Date'
                ];
                
                foreach ($donations as $donation) {
                    $data[] = [
                        $donation->donation_number,
                        $donation->establishment->business_name ?? 'Unknown',
                        $donation->item_name,
                        $donation->item_category,
                        $donation->quantity . ' ' . $donation->unit,
                        ucfirst(str_replace('_', ' ', $donation->status)),
                        $donation->scheduled_date->format('Y-m-d')
                    ];
                }
                break;
                
            case 'category':
                $filename = 'category_breakdown_' . date('Y-m-d_His') . '.csv';
                
                $donations = Donation::where('foodbank_id', $foodbankId)
                    ->with(['establishment'])
                    ->get();
                
                $categoryBreakdown = $donations->groupBy('item_category')->map(function ($categoryDonations) {
                    return [
                        'count' => $categoryDonations->count(),
                        'total_quantity' => $categoryDonations->sum('quantity'),
                        'by_status' => $categoryDonations->groupBy('status')->map->count(),
                        'establishments' => $categoryDonations->pluck('establishment_id')->unique()->count(),
                        'urgent_count' => $categoryDonations->where('is_urgent', true)->count(),
                        'expiring_count' => $categoryDonations->where('is_nearing_expiry', true)->count()
                    ];
                });
                
                $headers = [
                    'Category',
                    'Total Donations',
                    'Total Quantity',
                    'Establishments',
                    'Pending Pickup',
                    'Ready for Collection',
                    'Collected',
                    'Cancelled',
                    'Expired',
                    'Urgent Items',
                    'Expiring Soon'
                ];
                
                foreach ($categoryBreakdown as $category => $stats) {
                    $data[] = [
                        ucfirst($category),
                        $stats['count'],
                        $stats['total_quantity'],
                        $stats['establishments'],
                        $stats['by_status']['pending_pickup'] ?? 0,
                        $stats['by_status']['ready_for_collection'] ?? 0,
                        $stats['by_status']['collected'] ?? 0,
                        $stats['by_status']['cancelled'] ?? 0,
                        $stats['by_status']['expired'] ?? 0,
                        $stats['urgent_count'],
                        $stats['expiring_count']
                    ];
                }
                break;
        }
        
        // Generate CSV
        $callback = function() use ($headers, $data) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Write headers
            fputcsv($file, $headers);
            
            // Write data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ]);
    }

    /**
     * Export foodbank donation history in formatted formats (Excel, PDF)
     */
    private function exportFoodbankDonationHistoryFormatted(Request $request, $format)
    {
        $foodbankId = session('user_id');
        
        // Build queries with same filters as donationHistory - include both Donation and DonationRequest
        $donationQuery = Donation::where('foodbank_id', $foodbankId)
            ->with(['establishment']);
        
        $requestQuery = DonationRequest::where('foodbank_id', $foodbankId)
            ->whereNotNull('establishment_id') // Only establishment-submitted requests
            ->whereIn('status', [
                DonationRequestService::STATUS_ACCEPTED,
                DonationRequestService::STATUS_DECLINED,
                DonationRequestService::STATUS_COMPLETED
            ])
            ->with(['establishment', 'donation']);
        
        // Apply filters if provided
        if ($request->filled('status')) {
            $status = $request->status;
            $donationStatuses = ['pending_pickup', 'ready_for_collection', 'collected', 'cancelled', 'expired'];
            $requestStatuses = ['accepted', 'declined', 'completed'];
            
            if (in_array($status, $donationStatuses)) {
                $donationQuery->where('status', $status);
            } elseif (in_array($status, $requestStatuses)) {
                $requestQuery->where('status', $status);
            }
        }
        
        if ($request->filled('category')) {
            $donationQuery->where('item_category', $request->category);
            $requestQuery->where('category', $request->category);
        }
        
        if ($request->filled('establishment_id')) {
            $donationQuery->where('establishment_id', $request->establishment_id);
            $requestQuery->where('establishment_id', $request->establishment_id);
        }
        
        if ($request->filled('date_from')) {
            $donationQuery->where('scheduled_date', '>=', $request->date_from);
            $requestQuery->where('scheduled_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $donationQuery->where('scheduled_date', '<=', $request->date_to);
            $requestQuery->where('scheduled_date', '<=', $request->date_to);
        }
        
        // Get and format both types of records
        $allDonations = $donationQuery->orderBy('created_at', 'desc')->get();
        $allRequests = $requestQuery->orderBy('created_at', 'desc')->get();
        
        // Format records consistently
        $formattedRecords = collect();
        
        // Format Donation records
        foreach ($allDonations as $donation) {
            $formattedRecords->push([
                'donation_number' => $donation->donation_number,
                'item_name' => $donation->item_name,
                'category' => ucfirst($donation->item_category),
                'quantity' => $donation->quantity,
                'unit' => $donation->unit ?? 'pcs',
                'donation_type' => ucfirst($donation->pickup_method ?? 'pickup'),
                'date_donated' => $donation->created_at->format('Y-m-d H:i:s'),
                'scheduled_date' => $donation->scheduled_date ? $donation->scheduled_date->format('Y-m-d') : 'N/A',
                'scheduled_time' => $donation->scheduled_time ? (is_string($donation->scheduled_time) ? substr($donation->scheduled_time, 0, 5) : $donation->scheduled_time->format('H:i')) : 'N/A',
                'expiry_date' => $donation->expiry_date ? $donation->expiry_date->format('Y-m-d') : 'N/A',
                'collected_date' => $donation->collected_at ? $donation->collected_at->format('Y-m-d H:i:s') : 'N/A',
                'status' => ucfirst(str_replace('_', ' ', $donation->status)),
                'donor' => $donation->establishment->business_name ?? 'Unknown',
                'description' => $donation->description ?? '',
                'sort_date' => $donation->collected_at ?? $donation->created_at,
            ]);
        }
        
        // Format DonationRequest records
        foreach ($allRequests as $donationRequest) {
            $establishment = $donationRequest->establishment;
            $formatted = DonationRequestService::formatRequestData($donationRequest);
            
            $scheduledDate = $donationRequest->scheduled_date ?? $donationRequest->dropoff_date ?? $donationRequest->created_at;
            $scheduledDateFormatted = $scheduledDate instanceof \Carbon\Carbon 
                ? $scheduledDate->format('Y-m-d') 
                : ($scheduledDate ? \Carbon\Carbon::parse($scheduledDate)->format('Y-m-d') : 'N/A');
            
            // Format scheduled time - use time_display which handles allDay, anytime, or time ranges
            $scheduledTimeFormatted = $formatted['time_display'] ?? 'N/A';
            if ($scheduledTimeFormatted === 'N/A' && $formatted['scheduled_time_display'] && $formatted['scheduled_time_display'] !== 'N/A') {
                $scheduledTimeFormatted = $formatted['scheduled_time_display'];
            }
            
            // Format expiry date
            $expiryDateFormatted = 'N/A';
            if ($donationRequest->expiry_date) {
                $expiryDateFormatted = $donationRequest->expiry_date instanceof \Carbon\Carbon 
                    ? $donationRequest->expiry_date->format('Y-m-d') 
                    : \Carbon\Carbon::parse($donationRequest->expiry_date)->format('Y-m-d');
            }
            
            // Format collected date
            $collectedDateFormatted = 'N/A';
            if ($donationRequest->fulfilled_at) {
                $collectedDateFormatted = $donationRequest->fulfilled_at instanceof \Carbon\Carbon 
                    ? $donationRequest->fulfilled_at->format('Y-m-d H:i:s') 
                    : \Carbon\Carbon::parse($donationRequest->fulfilled_at)->format('Y-m-d H:i:s');
            } elseif ($donationRequest->status === DonationRequestService::STATUS_COMPLETED && $donationRequest->donation) {
                $donation = $donationRequest->donation;
                if ($donation->collected_at) {
                    $collectedDateFormatted = $donation->collected_at instanceof \Carbon\Carbon 
                        ? $donation->collected_at->format('Y-m-d H:i:s') 
                        : \Carbon\Carbon::parse($donation->collected_at)->format('Y-m-d H:i:s');
                }
            }
            
            $formattedRecords->push([
                'donation_number' => $donationRequest->donation ? $donationRequest->donation->donation_number : 'REQ-' . substr($donationRequest->donation_request_id, 0, 8),
                'item_name' => $donationRequest->item_name,
                'category' => ucfirst($donationRequest->category),
                'quantity' => $donationRequest->quantity,
                'unit' => $donationRequest->unit ?? 'pcs',
                'donation_type' => 'Pickup',
                'date_donated' => $donationRequest->created_at->format('Y-m-d H:i:s'),
                'scheduled_date' => $scheduledDateFormatted,
                'scheduled_time' => $scheduledTimeFormatted,
                'expiry_date' => $expiryDateFormatted,
                'collected_date' => $collectedDateFormatted,
                'status' => DonationRequestService::getStatusDisplay($donationRequest->status),
                'donor' => $establishment->business_name ?? 'Unknown',
                'description' => $donationRequest->description ?? '',
                'sort_date' => $donationRequest->fulfilled_at ?? $donationRequest->updated_at ?? $donationRequest->created_at,
            ]);
        }
        
        // Sort by date (most recent first)
        $formattedRecords = $formattedRecords->sortByDesc(function ($record) {
            return $record['sort_date'] instanceof \Carbon\Carbon 
                ? $record['sort_date']->timestamp 
                : \Carbon\Carbon::parse($record['sort_date'])->timestamp;
        })->values();
        
        if ($format === 'excel') {
            return $this->exportFoodbankToExcel($formattedRecords);
        } elseif ($format === 'pdf') {
            return $this->exportFoodbankToPdf($formattedRecords);
        }
        
        return redirect()->back()->with('error', 'Invalid export format.');
    }

    /**
     * Export foodbank donations to Excel
     */
    private function exportFoodbankToExcel($records)
    {
        $filename = 'donation_history_' . date('Y-m-d_His') . '.xlsx';
        
        $data = [];
        
        // Headers
        $data[] = [
            'Donation Number',
            'Item Name',
            'Category',
            'Quantity',
            'Unit',
            'Donation Type',
            'Date Donated',
            'Scheduled Date',
            'Scheduled Time',
            'Expiry Date',
            'Collected Date',
            'Status',
            'Donor (Establishment)',
            'Description'
        ];
        
        // Data rows
        foreach ($records as $record) {
            $data[] = [
                $record['donation_number'],
                $record['item_name'],
                $record['category'],
                $record['quantity'],
                $record['unit'],
                $record['donation_type'],
                $record['date_donated'],
                $record['scheduled_date'],
                $record['scheduled_time'],
                $record['expiry_date'],
                $record['collected_date'],
                $record['status'],
                $record['donor'],
                $record['description']
            ];
        }
        
        return Excel::create($filename, function($excel) use ($data) {
            $excel->sheet('Donation History', function($sheet) use ($data) {
                $sheet->fromArray($data, null, 'A1', false, false);
                
                // Style the header row
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                    $row->setBackground('#2d5016');
                    $row->setFontColor('#ffffff');
                });
                
                // Auto-size columns
                foreach(range('A', 'N') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            });
        })->export('xlsx');
    }

    /**
     * Export foodbank donations to PDF
     */
    private function exportFoodbankToPdf($records)
    {
        $user = $this->getUserData();
        $foodbankName = $user->organization_name ?? 'Foodbank';
        
        // Format data for PDF view
        $data = $records->map(function ($record) {
            return [
                'donation_number' => $record['donation_number'],
                'item_name' => $record['item_name'],
                'category' => $record['category'],
                'quantity' => $record['quantity'],
                'unit' => $record['unit'],
                'donation_type' => $record['donation_type'],
                'date_donated' => \Carbon\Carbon::parse($record['date_donated'])->format('F d, Y H:i'),
                'scheduled_date' => $record['scheduled_date'] !== 'N/A' ? \Carbon\Carbon::parse($record['scheduled_date'])->format('F d, Y') : 'N/A',
                'scheduled_time' => $record['scheduled_time'],
                'expiry_date' => $record['expiry_date'] !== 'N/A' ? \Carbon\Carbon::parse($record['expiry_date'])->format('F d, Y') : 'N/A',
                'collected_at' => $record['collected_date'] !== 'N/A' ? \Carbon\Carbon::parse($record['collected_date'])->format('F d, Y H:i') : 'N/A',
                'donor' => $record['donor'],
                'status' => $record['status'],
                'description' => $record['description'] ?: 'N/A',
            ];
        })->toArray();

        $filename = 'donation_history_' . date('Y-m-d_His') . '.pdf';
        
        $pdf = Pdf::loadView('foodbank.donation-history-pdf', [
            'data' => $data,
            'foodbankName' => $foodbankName,
            'exportDate' => now()->format('F d, Y'),
            'totalDonations' => count($data)
        ]);
        
        return $pdf->download($filename);
    }

    /**
     * Show impact reports page for foodbank
     */
    public function foodbankImpactReports()
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return redirect()->route('login')->with('error', 'Access denied. Please login as a foodbank.');
        }
        
        $user = $this->getUserData();
        $foodbankId = session('user_id');
        $foodbank = Foodbank::find($foodbankId);
        $isVerified = $foodbank && $foodbank->isVerified();
        
        // Dynamic Food Requests - count of all donation requests for this foodbank
        $foodRequests = DonationRequest::where('foodbank_id', $foodbankId)->count();
        
        // Dynamic Food Received - count of completed/collected donations
        $foodReceived = Donation::where('foodbank_id', $foodbankId)
            ->where('status', 'collected')
            ->count();
        
        // Dynamic Chart Data - quantity of received items (status = 'collected')
        // Daily data (last 7 days)
        $dailyData = [];
        $dayLabels = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayName = $dayLabels[$date->dayOfWeek];
            
            $dayQuantity = Donation::where('foodbank_id', $foodbankId)
                ->where('status', 'collected')
                ->whereDate('collected_at', $date->toDateString())
                ->sum('quantity');
            
            $dailyData[] = [
                'label' => $dayName,
                'value' => (int) $dayQuantity
            ];
        }
        
        // Monthly data (last 12 months)
        $monthlyData = [];
        $monthLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $monthLabels[$date->month - 1];
            
            $monthQuantity = Donation::where('foodbank_id', $foodbankId)
                ->where('status', 'collected')
                ->whereYear('collected_at', $date->year)
                ->whereMonth('collected_at', $date->month)
                ->sum('quantity');
            
            $monthlyData[] = [
                'label' => $monthName,
                'value' => (int) $monthQuantity
            ];
        }
        
        // Yearly data (last 5 years)
        $yearlyData = [];
        for ($i = 4; $i >= 0; $i--) {
            $year = now()->subYears($i)->year;
            
            $yearQuantity = Donation::where('foodbank_id', $foodbankId)
                ->where('status', 'collected')
                ->whereYear('collected_at', $year)
                ->sum('quantity');
            
            $yearlyData[] = [
                'label' => (string) $year,
                'value' => (int) $yearQuantity
            ];
        }
        
        // Top Establishment Contributors - based on completed/collected donation requests
        $foodbankId = session('user_id');
        $topContributors = DonationRequest::where('foodbank_id', $foodbankId)
            ->where('status', DonationRequestService::STATUS_COMPLETED)
            ->with('establishment')
            ->get()
            ->groupBy('establishment_id')
            ->map(function ($requests) {
                $establishment = $requests->first()->establishment;
                $completedRequestsCount = $requests->count();
                return [
                    'establishment_id' => $requests->first()->establishment_id,
                    'establishment_name' => $establishment->business_name ?? 'Unknown',
                    'completed_requests' => $completedRequestsCount,
                ];
            })
            ->sortByDesc('completed_requests')
            ->take(5)
            ->values();
        
        // Calculate total completed requests for percentage calculation
        $totalCompletedRequests = DonationRequest::where('foodbank_id', $foodbankId)
            ->where('status', DonationRequestService::STATUS_COMPLETED)
            ->count();
        
        // Calculate percentages and assign colors
        $colors = ['#f5cd79', '#ff6b6b', '#7AB267', '#347928', '#9DCF86'];
        $topContributorsData = $topContributors->map(function ($contributor, $index) use ($totalCompletedRequests, $colors) {
            $percentage = $totalCompletedRequests > 0 ? ($contributor['completed_requests'] / $totalCompletedRequests) * 100 : 0;
            return [
                'rank' => $index + 1,
                'establishment_name' => $contributor['establishment_name'],
                'completed_requests' => $contributor['completed_requests'],
                'percentage' => round($percentage, 2),
                'color' => $colors[$index % count($colors)],
            ];
        })->toArray();
        
        // Dynamic Reports data - empty for now, can be populated from export history if available
        $reports = [];
        
        return view('foodbank.impact-reports', compact(
            'user',
            'foodRequests',
            'foodReceived',
            'dailyData',
            'monthlyData',
            'yearlyData',
            'topContributorsData',
            'reports'
        ));
    }

    /**
     * Show admin dashboard
     */
    public function admin()
    {
        // Verify user is an admin
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied. Please login as an admin.');
        }
        
        $user = $this->getUserData();
        
        // ============================================
        // USER STATISTICS BY ROLE (CACHED)
        // ============================================
        $userCounts = DashboardCacheService::getUserCounts();
        $totalConsumers = $userCounts['consumers'];
        $totalEstablishments = $userCounts['establishments'];
        $totalFoodbanks = $userCounts['foodbanks'];
        $totalUsers = DashboardCacheService::getTotalUsers();
        
        // ============================================
        // FOOD LISTINGS STATISTICS (CACHED)
        // ============================================
        $totalActiveListings = DashboardCacheService::getActiveListings();
        $totalListings = DashboardCacheService::getTotalListings();
        
        // ============================================
        // ORDERS STATISTICS BY STATUS (CACHED)
        // ============================================
        $ordersByStatus = DashboardCacheService::getOrdersByStatus();
        $totalOrders = DashboardCacheService::getTotalOrders();
        
        // ============================================
        // DONATIONS STATISTICS (CACHED)
        // ============================================
        $totalDonations = DashboardCacheService::getTotalDonations();
        $donationStatuses = DashboardCacheService::getDonationStatuses();
        $completedDonations = $donationStatuses['completed'];
        $pendingDonations = $donationStatuses['pending'];
        
        // ============================================
        // FOOD RESCUED STATISTICS (CACHED)
        // ============================================
        $foodRescuedData = DashboardCacheService::getFoodRescued();
        $totalFoodRescued = $foodRescuedData['total'];
        $foodRescuedFromOrders = $foodRescuedData['from_orders'];
        $foodRescuedFromDonations = $foodRescuedData['from_donations'];
        
        // Format food rescued
        $foodRescuedFormatted = $totalFoodRescued >= 1000 
            ? number_format($totalFoodRescued / 1000, 1) . 'K' 
            : number_format($totalFoodRescued);
        
        // ============================================
        // MONTHLY ACTIVITY DATA (CACHED)
        // ============================================
        $monthlyData = DashboardCacheService::getMonthlyActivity();
        $monthlyActivity = $monthlyData['activity'];
        $months = $monthlyData['months'];
        
        // ============================================
        // RECENT ACTIVITY (NOT CACHED - changes frequently)
        // ============================================
        $recentConsumers = Consumer::orderBy('created_at', 'desc')->limit(3)->get();
        $recentEstablishments = Establishment::orderBy('created_at', 'desc')->limit(3)->get();
        $recentDonations = Donation::whereIn('status', ['collected', 'ready_for_collection'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Combine all activities and sort by created_at descending (newest first)
        $allActivities = collect();
        
        // Add establishments with type identifier
        foreach ($recentEstablishments as $establishment) {
            $allActivities->push([
                'type' => 'establishment',
                'data' => $establishment,
                'created_at' => $establishment->created_at,
            ]);
        }
        
        // Add consumers with type identifier
        foreach ($recentConsumers as $consumer) {
            $allActivities->push([
                'type' => 'consumer',
                'data' => $consumer,
                'created_at' => $consumer->created_at,
            ]);
        }
        
        // Add donations with type identifier
        foreach ($recentDonations as $donation) {
            $allActivities->push([
                'type' => 'donation',
                'data' => $donation,
                'created_at' => $donation->created_at,
            ]);
        }
        
        // Sort by created_at descending (newest first) and take top 5
        $recentActivities = $allActivities->sortByDesc('created_at')->take(5)->values();
        
        // ============================================
        // USER ENGAGEMENT (CACHED)
        // ============================================
        $engagementData = Cache::remember('dashboard:admin:engagement', DashboardCacheService::CACHE_TTL * 60, function () use ($totalUsers) {
            $thirtyDaysAgo = Carbon::now()->subDays(30);
            $activeConsumers = Consumer::where('created_at', '>=', $thirtyDaysAgo)->count();
            $activeEstablishments = Establishment::where('created_at', '>=', $thirtyDaysAgo)->count();
            $activeFoodbanks = Foodbank::where('created_at', '>=', $thirtyDaysAgo)->count();
            $totalActiveUsers = $activeConsumers + $activeEstablishments + $activeFoodbanks;
            $engagementPercentage = $totalUsers > 0 ? round(($totalActiveUsers / $totalUsers) * 100) : 0;
            
            return [
                'active_consumers' => $activeConsumers,
                'active_establishments' => $activeEstablishments,
                'active_foodbanks' => $activeFoodbanks,
                'total_active_users' => $totalActiveUsers,
                'engagement_percentage' => $engagementPercentage,
            ];
        });
        $engagementPercentage = $engagementData['engagement_percentage'];
        
        return view('admin.dashboard', compact(
            'user',
            'totalUsers',
            'totalConsumers',
            'totalEstablishments',
            'totalFoodbanks',
            'totalActiveListings',
            'totalListings',
            'ordersByStatus',
            'totalOrders',
            'totalDonations',
            'completedDonations',
            'pendingDonations',
            'totalFoodRescued',
            'foodRescuedFormatted',
            'foodRescuedFromOrders',
            'foodRescuedFromDonations',
            'monthlyActivity',
            'months',
            'recentConsumers',
            'recentEstablishments',
            'recentDonations',
            'recentActivities',
            'engagementPercentage'
        ));
    }

    /**
     * Admin - User Management
     */
    public function adminUsers(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        
        // Get filter parameters
        $roleFilter = $request->get('role', 'all');
        $statusFilter = $request->get('status', 'all');
        $searchQuery = $request->get('search', '');
        
        // Initialize user collections
        $allUsers = collect();
        
        // Fetch consumers
        if ($roleFilter === 'all' || $roleFilter === 'consumer') {
            $consumers = Consumer::query();
            
            if ($statusFilter !== 'all') {
                $consumers->where('status', $statusFilter);
            }
            
            if ($searchQuery) {
                $consumers->where(function($query) use ($searchQuery) {
                    $query->where('fname', 'like', "%{$searchQuery}%")
                          ->orWhere('lname', 'like', "%{$searchQuery}%")
                          ->orWhere('email', 'like', "%{$searchQuery}%")
                          ->orWhere('username', 'like', "%{$searchQuery}%");
                });
            }
            
            $consumers = $consumers->get()->map(function($consumer) {
                return [
                    'id' => $consumer->consumer_id,
                    'name' => $consumer->fname . ' ' . $consumer->lname,
                    'email' => $consumer->email,
                    'username' => $consumer->username,
                    'phone' => $consumer->phone_no,
                    'address' => $consumer->address,
                    'role' => 'consumer',
                    'status' => $consumer->status ?? 'active',
                    'registered_at' => $consumer->created_at,
                    'profile_image' => $consumer->profile_image,
                ];
            });
            
            $allUsers = $allUsers->merge($consumers);
        }
        
        // Fetch establishments
        if ($roleFilter === 'all' || $roleFilter === 'establishment') {
            $establishments = Establishment::query();
            
            if ($statusFilter !== 'all') {
                $establishments->where('status', $statusFilter);
            }
            
            if ($searchQuery) {
                $establishments->where(function($query) use ($searchQuery) {
                    $query->where('business_name', 'like', "%{$searchQuery}%")
                          ->orWhere('email', 'like', "%{$searchQuery}%")
                          ->orWhere('username', 'like', "%{$searchQuery}%")
                          ->orWhere('owner_fname', 'like', "%{$searchQuery}%")
                          ->orWhere('owner_lname', 'like', "%{$searchQuery}%");
                });
            }
            
            $establishments = $establishments->get()->map(function($establishment) {
                return [
                    'id' => $establishment->establishment_id,
                    'name' => $establishment->business_name,
                    'email' => $establishment->email,
                    'username' => $establishment->username,
                    'phone' => $establishment->phone_no,
                    'address' => $establishment->address,
                    'role' => 'establishment',
                    'status' => $establishment->status ?? 'active',
                    'registered_at' => $establishment->created_at,
                    'profile_image' => $establishment->profile_image,
                    'business_type' => $establishment->business_type,
                ];
            });
            
            $allUsers = $allUsers->merge($establishments);
        }
        
        // Fetch foodbanks
        if ($roleFilter === 'all' || $roleFilter === 'foodbank') {
            $foodbanks = Foodbank::query();
            
            if ($statusFilter !== 'all') {
                $foodbanks->where('status', $statusFilter);
            }
            
            if ($searchQuery) {
                $foodbanks->where(function($query) use ($searchQuery) {
                    $query->where('organization_name', 'like', "%{$searchQuery}%")
                          ->orWhere('email', 'like', "%{$searchQuery}%")
                          ->orWhere('username', 'like', "%{$searchQuery}%")
                          ->orWhere('contact_person', 'like', "%{$searchQuery}%");
                });
            }
            
            $foodbanks = $foodbanks->get()->map(function($foodbank) {
                return [
                    'id' => $foodbank->foodbank_id,
                    'name' => $foodbank->organization_name,
                    'email' => $foodbank->email,
                    'username' => $foodbank->username,
                    'phone' => $foodbank->phone_no,
                    'address' => $foodbank->address,
                    'role' => 'foodbank',
                    'status' => $foodbank->status ?? 'active',
                    'registered_at' => $foodbank->created_at,
                    'profile_image' => $foodbank->profile_image,
                    'contact_person' => $foodbank->contact_person,
                ];
            });
            
            $allUsers = $allUsers->merge($foodbanks);
        }
        
        // Sort by registered date (newest first)
        $allUsers = $allUsers->sortByDesc('registered_at')->values();
        
        // Statistics
        $stats = [
            'total' => Consumer::count() + Establishment::count() + Foodbank::count(),
            'consumers' => Consumer::count(),
            'establishments' => Establishment::count(),
            'foodbanks' => Foodbank::count(),
            'active' => Consumer::where('status', 'active')->count() + 
                       Establishment::where('status', 'active')->count() + 
                       Foodbank::where('status', 'active')->count(),
            'suspended' => Consumer::where('status', 'suspended')->count() + 
                          Establishment::where('status', 'suspended')->count() + 
                          Foodbank::where('status', 'suspended')->count(),
        ];
        
        return view('admin.users', compact('user', 'allUsers', 'stats', 'roleFilter', 'statusFilter', 'searchQuery'));
    }
    
    /**
     * Admin - Update User Status
     */
    public function updateUserStatus(Request $request, $role, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        $request->validate([
            'status' => 'required|in:active,suspended,deleted'
        ]);
        
        try {
            $model = match($role) {
                'consumer' => Consumer::find($id),
                'establishment' => Establishment::find($id),
                'foodbank' => Foodbank::find($id),
                default => null
            };
            
            if (!$model) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }
            
            $model->status = $request->status;
            $model->save();
            
            $action = match($request->status) {
                'active' => 'activated',
                'suspended' => 'suspended',
                'deleted' => 'deleted',
                default => 'updated'
            };
            
            return response()->json([
                'success' => true,
                'message' => "User {$action} successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Admin - Update User Information
     */
    public function updateUserInfo(Request $request, $role, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        try {
            $model = match($role) {
                'consumer' => Consumer::find($id),
                'establishment' => Establishment::find($id),
                'foodbank' => Foodbank::find($id),
                default => null
            };
            
            if (!$model) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }
            
            // Validate based on role
            $rules = [];
            if ($role === 'consumer') {
                $rules = [
                    'fname' => 'required|string|max:255',
                    'lname' => 'required|string|max:255',
                    'email' => 'required|email|unique:consumers,email,' . $id . ',consumer_id',
                    'phone_no' => 'nullable|string|max:20',
                ];
            } elseif ($role === 'establishment') {
                $rules = [
                    'business_name' => 'required|string|max:255',
                    'email' => 'required|email|unique:establishments,email,' . $id . ',establishment_id',
                    'phone_no' => 'nullable|string|max:20',
                ];
            } elseif ($role === 'foodbank') {
                $rules = [
                    'organization_name' => 'required|string|max:255',
                    'email' => 'required|email|unique:foodbanks,email,' . $id . ',foodbank_id',
                    'phone_no' => 'nullable|string|max:20',
                ];
            }
            
            $validated = $request->validate($rules);
            
            // Update user
            $model->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'User information updated successfully.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user information.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Admin - Delete User
     * Deletes personal content and anonymizes critical business records
     */
    public function deleteUser($role, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        try {
            \DB::beginTransaction();
            
            $model = match($role) {
                'consumer' => Consumer::find($id),
                'establishment' => Establishment::find($id),
                'foodbank' => Foodbank::find($id),
                default => null
            };
            
            if (!$model) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }
            
            $userId = $model->getKey();
            $userName = $this->getUserName($model, $role);
            
            // Step 1: Delete personal content
            $this->deletePersonalContent($model, $role);
            
            // Step 2: Anonymize critical business records
            $this->anonymizeCriticalRecords($model, $role, $userId, $userName);
            
            // Step 3: Delete the user record
            $model->delete();
            
            \DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully. Personal content removed and business records anonymized.'
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('User deletion failed: ' . $e->getMessage(), [
                'role' => $role,
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get user name for anonymization
     */
    private function getUserName($model, $role)
    {
        return match($role) {
            'consumer' => trim(($model->fname ?? '') . ' ' . ($model->lname ?? '')),
            'establishment' => $model->business_name ?? trim(($model->owner_fname ?? '') . ' ' . ($model->owner_lname ?? '')),
            'foodbank' => $model->organization_name ?? $model->contact_person ?? 'Foodbank',
            default => 'User'
        };
    }
    
    /**
     * Delete personal content (reviews, food listings, notifications)
     */
    private function deletePersonalContent($model, $role)
    {
        $userId = $model->getKey();
        
        if ($role === 'consumer') {
            // Delete reviews written by consumer
            Review::where('consumer_id', $userId)->delete();
            
            // Delete notifications for consumer
            Notification::where('user_id', $userId)
                ->where('user_type', 'consumer')
                ->delete();
        } elseif ($role === 'establishment') {
            // Delete food listings created by establishment
            FoodListing::where('establishment_id', $userId)->delete();
            
            // Delete reviews about this establishment (personal opinions)
            Review::where('establishment_id', $userId)->delete();
            
            // Delete notifications for establishment
            Notification::where('user_id', $userId)
                ->where('user_type', 'establishment')
                ->delete();
        } elseif ($role === 'foodbank') {
            // Delete notifications for foodbank
            Notification::where('user_id', $userId)
                ->where('user_type', 'foodbank')
                ->delete();
        }
    }
    
    /**
     * Anonymize critical business records (orders, donations, transactions, stock ledger)
     */
    private function anonymizeCriticalRecords($model, $role, $userId, $userName)
    {
        if ($role === 'consumer') {
            // Anonymize orders
            $orders = Order::where('consumer_id', $userId)->get();
            foreach ($orders as $order) {
                // Delete stock ledger entries for these orders
                StockLedger::where('order_id', $order->id)->delete();
            }
            
            Order::where('consumer_id', $userId)
                ->update([
                    'consumer_id' => null,
                    'customer_name' => 'Deleted User',
                    'customer_phone' => null,
                ]);
        } elseif ($role === 'establishment') {
            // Get orders before anonymizing
            $orders = Order::where('establishment_id', $userId)->get();
            foreach ($orders as $order) {
                // Delete stock ledger entries for these orders
                StockLedger::where('order_id', $order->id)->delete();
            }
            
            // Anonymize orders
            Order::where('establishment_id', $userId)
                ->update([
                    'establishment_id' => null,
                ]);
            
            // Anonymize donations
            Donation::where('establishment_id', $userId)
                ->update([
                    'establishment_id' => null,
                    'establishment_notes' => 'Donation from deleted account',
                ]);
        } elseif ($role === 'foodbank') {
            // Anonymize donations
            Donation::where('foodbank_id', $userId)
                ->update([
                    'foodbank_id' => null,
                    'foodbank_notes' => 'Donation to deleted account',
                ]);
            
            // Anonymize donation requests
            DonationRequest::where('foodbank_id', $userId)
                ->update([
                    'foodbank_id' => null,
                    'contact_name' => 'Deleted Account',
                    'email' => 'deleted@account.local',
                    'phone_number' => null,
                ]);
        }
    }

    /**
     * Admin - Establishments Management
     */
    public function adminEstablishments(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        
        // Get filter parameters
        $searchQuery = $request->get('search', '');
        $statusFilter = $request->get('status', 'all');
        $verifiedFilter = $request->get('verified', 'all');
        
        // Optimized query with eager loading
        $establishmentsQuery = Establishment::withCount([
            'foodListings as active_listings_count' => function($query) {
                $query->where('food_listings.status', 'active');
            },
            'foodListings as total_listings_count'
        ])
        ->with(['foodListings' => function($query) {
            $query->select('establishment_id', 'status')
                  ->where('status', 'active')
                  ->limit(1); // Just to check if has listings
        }]);
        
        // Apply search filter
        if ($searchQuery) {
            $establishmentsQuery->where(function($query) use ($searchQuery) {
                $query->where('business_name', 'like', "%{$searchQuery}%")
                      ->orWhere('email', 'like', "%{$searchQuery}%")
                      ->orWhere('username', 'like', "%{$searchQuery}%")
                      ->orWhere('owner_fname', 'like', "%{$searchQuery}%")
                      ->orWhere('owner_lname', 'like', "%{$searchQuery}%");
            });
        }
        
        // Apply status filter
        if ($statusFilter !== 'all') {
            $establishmentsQuery->where('status', $statusFilter);
        }
        
        // Apply verified filter
        if ($verifiedFilter !== 'all') {
            $establishmentsQuery->where('verification_status', $verifiedFilter);
        }
        
        // Get establishments with pagination for large datasets
        $establishments = $establishmentsQuery->orderBy('created_at', 'desc')->get();
        
        // Calculate ratings for each establishment (optimized)
        $establishmentIds = $establishments->pluck('establishment_id')->toArray();
        $ratings = Review::whereIn('establishment_id', $establishmentIds)
            ->selectRaw('establishment_id, AVG(rating) as avg_rating, COUNT(*) as total_reviews')
            ->groupBy('establishment_id')
            ->get()
            ->keyBy('establishment_id');
        
        // Format establishments data
        $formattedEstablishments = $establishments->map(function($establishment) use ($ratings) {
            $ratingData = $ratings->get($establishment->establishment_id);
            
            return [
                'id' => $establishment->establishment_id,
                'business_name' => $establishment->business_name,
                'owner_name' => $establishment->owner_fname . ' ' . $establishment->owner_lname,
                'email' => $establishment->email,
                'phone' => $establishment->phone_no,
                'address' => $establishment->address,
                'business_type' => $establishment->business_type,
                'username' => $establishment->username,
                'status' => $establishment->status ?? 'active',
                'verified' => ($establishment->verification_status ?? 'unverified') === 'verified',
                'active_listings' => $establishment->active_listings_count ?? 0,
                'total_listings' => $establishment->total_listings_count ?? 0,
                'avg_rating' => $ratingData ? round($ratingData->avg_rating, 1) : 0,
                'total_reviews' => $ratingData ? $ratingData->total_reviews : 0,
                'registered_at' => $establishment->created_at,
                'profile_image' => $establishment->profile_image,
            ];
        });
        
        // Statistics
        $stats = [
            'total' => Establishment::count(),
            'verified' => Establishment::where('verification_status', 'verified')->count(),
            'unverified' => Establishment::where('verification_status', 'unverified')->count(),
            'active' => Establishment::where('status', 'active')->count(),
            'suspended' => Establishment::where('status', 'suspended')->count(),
        ];
        
        return view('admin.establishments', compact(
            'user',
            'formattedEstablishments',
            'stats',
            'searchQuery',
            'statusFilter',
            'verifiedFilter'
        ));
    }

    /**
     * Admin - Get Establishment Details
     */
    public function getEstablishmentDetails($id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        try {
            $establishment = Establishment::withCount([
                'foodListings as active_listings_count' => function($query) {
                    $query->where('food_listings.status', 'active');
                },
                'foodListings as total_listings_count',
                'orders as total_orders_count',
                'reviews as total_reviews_count'
            ])->find($id);

            if (!$establishment) {
                return response()->json(['success' => false, 'message' => 'Establishment not found.'], 404);
            }

            // Calculate average rating
            $ratingData = Review::where('establishment_id', $establishment->establishment_id)
                ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total_reviews')
                ->first();

            // Get recent orders (last 5)
            $recentOrders = $establishment->orders()
                ->with(['foodListing', 'consumer'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Format establishment data
            $details = [
                'id' => $establishment->establishment_id,
                'business_name' => $establishment->business_name,
                'owner_fname' => $establishment->owner_fname,
                'owner_lname' => $establishment->owner_lname,
                'owner_name' => $establishment->owner_fname . ' ' . $establishment->owner_lname,
                'email' => $establishment->email,
                'username' => $establishment->username,
                'phone_no' => $establishment->phone_no,
                'address' => $establishment->address,
                'formatted_address' => $establishment->formatted_address,
                'latitude' => $establishment->latitude,
                'longitude' => $establishment->longitude,
                'business_type' => $establishment->business_type,
                'status' => $establishment->status ?? 'active',
                'verification_status' => $establishment->verification_status ?? 'unverified',
                'verified' => ($establishment->verification_status ?? 'unverified') === 'verified',
                'profile_image' => $establishment->profile_image,
                'bir_file' => $establishment->bir_file,
                'registered_at' => $establishment->created_at ? $establishment->created_at->format('F j, Y g:i A') : 'N/A',
                'updated_at' => $establishment->updated_at ? $establishment->updated_at->format('F j, Y g:i A') : 'N/A',
                'active_listings' => $establishment->active_listings_count ?? 0,
                'total_listings' => $establishment->total_listings_count ?? 0,
                'total_orders' => $establishment->total_orders_count ?? 0,
                'avg_rating' => $ratingData ? round($ratingData->avg_rating, 1) : 0,
                'total_reviews' => $ratingData ? $ratingData->total_reviews : 0,
                'violations_count' => $establishment->violations_count ?? 0,
                'violations' => $establishment->violations ?? [],
                'recent_orders' => $recentOrders->map(function($order) {
                    return [
                        'order_number' => $order->order_number,
                        'item_name' => $order->foodListing->name ?? 'N/A',
                        'quantity' => $order->quantity,
                        'total_price' => $order->total_price,
                        'status' => $order->status,
                        'created_at' => $order->created_at->format('M j, Y g:i A'),
                        'customer_name' => $order->customer_name,
                    ];
                })->toArray(),
            ];

            return response()->json([
                'success' => true,
                'data' => $details
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching establishment details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch establishment details.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.'
            ], 500);
        }
    }
    
    /**
     * Admin - Update Establishment Status
     */
    public function updateEstablishmentStatus(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        $request->validate([
            'status' => 'required|in:active,suspended,deleted'
        ]);
        
        try {
            $establishment = Establishment::find($id);
            
            if (!$establishment) {
                return response()->json(['success' => false, 'message' => 'Establishment not found.'], 404);
            }
            
            $oldStatus = $establishment->status;
            $establishment->status = $request->status;
            $establishment->save();
            
            $action = match($request->status) {
                'active' => 'activated',
                'suspended' => 'suspended',
                'deleted' => 'deleted',
                default => 'updated'
            };
            
            // Log action
            \App\Models\SystemLog::log(
                'admin_action',
                'establishment_status_updated',
                "Admin {$action} establishment: {$establishment->business_name}",
                'info',
                'success',
                [
                    'establishment_id' => $establishment->establishment_id,
                    'establishment_name' => $establishment->business_name,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'admin_id' => session('user_id'),
                    'admin_email' => session('user_email'),
                ]
            );
            
            // Send notification
            if ($request->status === 'suspended') {
                \App\Models\Notification::createNotification(
                    'establishment',
                    $establishment->establishment_id,
                    'account_suspended',
                    'Account Suspended',
                    "Your establishment account has been suspended by an administrator.",
                    ['establishment_id' => $establishment->establishment_id]
                );
            } elseif ($request->status === 'active' && $oldStatus === 'suspended') {
                \App\Models\Notification::createNotification(
                    'establishment',
                    $establishment->establishment_id,
                    'account_unsuspended',
                    'Account Unsuspended',
                    "Your establishment account has been reactivated.",
                    ['establishment_id' => $establishment->establishment_id]
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => "Establishment {$action} successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update establishment status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Admin - Verify/Unverify Establishment
     */
    public function toggleEstablishmentVerification(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        $request->validate([
            'verification_status' => 'required|in:verified,unverified'
        ]);
        
        try {
            $establishment = Establishment::find($id);
            
            if (!$establishment) {
                return response()->json(['success' => false, 'message' => 'Establishment not found.'], 404);
            }
            
            $oldStatus = $establishment->verification_status ?? 'unverified';
            $newStatus = $request->verification_status;
            
            // Update verification status
            $establishment->verification_status = $newStatus;
            // Also update legacy verified field for backward compatibility
            $establishment->verified = ($newStatus === 'verified');
            $establishment->save();
            
            // Hide/restore food listings based on verification status
            if ($oldStatus === 'verified' && $newStatus === 'unverified') {
                // Hide all active food listings (set to inactive)
                FoodListing::where('establishment_id', $establishment->establishment_id)
                    ->where('status', 'active')
                    ->update(['status' => 'inactive']);
                
                // Send notification to establishment about unverification
                try {
                    Notification::createNotification(
                        'establishment',
                        $establishment->establishment_id,
                        'account_unverified',
                        'Account Unverified',
                        "Your establishment account verification has been removed. Your food listings have been hidden and you may have limited access to certain features.",
                        [
                            'establishment_id' => $establishment->establishment_id,
                            'data' => [
                                'business_name' => $establishment->business_name,
                                'verification_status' => 'unverified',
                            ]
                        ]
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send unverification notification: ' . $e->getMessage());
                }
            } elseif ($oldStatus === 'unverified' && $newStatus === 'verified') {
                // Restore all inactive food listings (set to active)
                FoodListing::where('establishment_id', $establishment->establishment_id)
                    ->where('status', 'inactive')
                    ->update(['status' => 'active']);
                
                // Send notification to establishment about verification
                try {
                    Notification::createNotification(
                        'establishment',
                        $establishment->establishment_id,
                        'account_verified',
                        'Account Verified',
                        "Congratulations! Your establishment account has been verified by an administrator. You now have full access to all features, including posting food listings and managing your business.",
                        [
                            'establishment_id' => $establishment->establishment_id,
                            'data' => [
                                'business_name' => $establishment->business_name,
                                'verification_status' => 'verified',
                            ]
                        ]
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send verification notification: ' . $e->getMessage());
                }
            }
            
            // Determine action for logging and response
            $action = $newStatus === 'verified' ? 'verified' : 'unverified';
            
            // Log the action
            \App\Models\SystemLog::log(
                'admin_action',
                'establishment_verification_updated',
                "Admin {$action} establishment: {$establishment->business_name}",
                'info',
                'success',
                [
                    'establishment_id' => $establishment->establishment_id,
                    'establishment_name' => $establishment->business_name,
                    'old_verification_status' => $oldStatus,
                    'new_verification_status' => $newStatus,
                    'admin_id' => session('user_id'),
                    'admin_email' => session('user_email'),
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => "Establishment {$action} successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update verification status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Admin - Add Violation to Establishment
     */
    public function addEstablishmentViolation(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        $request->validate([
            'violation_type' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:low,medium,high'
        ]);
        
        try {
            $establishment = Establishment::find($id);
            
            if (!$establishment) {
                return response()->json(['success' => false, 'message' => 'Establishment not found.'], 404);
            }
            
            $violations = $establishment->violations ?? [];
            $violations[] = [
                'type' => $request->violation_type,
                'description' => $request->description,
                'severity' => $request->severity,
                'date' => now()->toDateString(),
                'admin' => session('user_name'),
            ];
            
            $establishment->violations = $violations;
            $establishment->violations_count = count($violations);
            $establishment->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Violation added successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add violation.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Admin - Delete Establishment
     */
    public function deleteEstablishment($id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        try {
            $establishment = Establishment::find($id);
            
            if (!$establishment) {
                return response()->json(['success' => false, 'message' => 'Establishment not found.'], 404);
            }
            
            // Soft delete by setting status to deleted
            $establishment->status = 'deleted';
            $establishment->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Establishment deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete establishment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin - Food Listings Management
     */
    public function adminFoodListings(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        
        // Get filter parameters
        $searchQuery = $request->get('search', '');
        $categoryFilter = $request->get('category', 'all');
        $statusFilter = $request->get('status', 'all');
        $expiryFilter = $request->get('expiry', 'all');
        
        // Optimized query with eager loading
        $listingsQuery = FoodListing::with(['establishment' => function($query) {
            $query->select('establishment_id', 'business_name', 'email', 'status');
        }])
        ->select('food_listings.*');
        
        // Apply search filter
        if ($searchQuery) {
            $listingsQuery->where(function($query) use ($searchQuery) {
                $query->where('food_listings.name', 'like', "%{$searchQuery}%")
                      ->orWhere('food_listings.description', 'like', "%{$searchQuery}%")
                      ->orWhereHas('establishment', function($q) use ($searchQuery) {
                          $q->where('business_name', 'like', "%{$searchQuery}%")
                            ->orWhere('email', 'like', "%{$searchQuery}%");
                      });
            });
        }
        
        // Apply category filter
        if ($categoryFilter !== 'all') {
            $listingsQuery->where('food_listings.category', $categoryFilter);
        }
        
        // Apply status filter
        if ($statusFilter !== 'all') {
            $listingsQuery->where('food_listings.status', $statusFilter);
        }
        
        // Apply expiry filter
        if ($expiryFilter !== 'all') {
            $today = now()->toDateString();
            if ($expiryFilter === 'expired') {
                $listingsQuery->where('food_listings.expiry_date', '<', $today);
            } elseif ($expiryFilter === 'expiring_soon') {
                $nextWeek = now()->addWeek()->toDateString();
                $listingsQuery->whereBetween('food_listings.expiry_date', [$today, $nextWeek]);
            } elseif ($expiryFilter === 'active') {
                $listingsQuery->where('food_listings.expiry_date', '>=', $today);
            }
        }
        
        // Get listings ordered by creation date
        $listings = $listingsQuery->orderBy('food_listings.created_at', 'desc')->get();
        
        // Format listings data
        $formattedListings = $listings->map(function($listing) {
            $isExpired = $listing->expiry_date < now()->toDateString();
            $daysUntilExpiry = $isExpired ? 0 : now()->diffInDays($listing->expiry_date, false);
            
            return [
                'id' => $listing->id,
                'name' => $listing->name,
                'description' => $listing->description,
                'category' => $listing->category,
                'quantity' => $listing->quantity,
                'reserved_stock' => $listing->reserved_stock ?? 0,
                'sold_stock' => $listing->sold_stock ?? 0,
                'available_stock' => $listing->available_stock,
                'original_price' => $listing->original_price,
                'discount_percentage' => $listing->discount_percentage,
                'discounted_price' => $listing->discounted_price,
                'expiry_date' => $listing->expiry_date,
                'is_expired' => $isExpired,
                'days_until_expiry' => $daysUntilExpiry,
                'status' => $listing->status,
                'image_path' => $listing->image_path,
                'establishment' => $listing->establishment ? [
                    'id' => $listing->establishment->establishment_id,
                    'name' => $listing->establishment->business_name,
                    'email' => $listing->establishment->email,
                    'status' => $listing->establishment->status,
                ] : null,
                'created_at' => $listing->created_at,
            ];
        });
        
        // Get unique categories for filter
        $categories = FoodListing::distinct()->pluck('category')->filter()->sort()->values();
        
        // Statistics
        $stats = [
            'total' => FoodListing::count(),
            'active' => FoodListing::where('status', 'active')->count(),
            'inactive' => FoodListing::where('status', 'inactive')->count(),
            'expired' => FoodListing::where('expiry_date', '<', now()->toDateString())->count(),
            'expiring_soon' => FoodListing::whereBetween('expiry_date', [now()->toDateString(), now()->addWeek()->toDateString()])->count(),
        ];
        
        return view('admin.food-listings', compact(
            'user',
            'formattedListings',
            'categories',
            'stats',
            'searchQuery',
            'categoryFilter',
            'statusFilter',
            'expiryFilter'
        ));
    }
    
    /**
     * Admin - Update Food Listing Status
     */
    public function updateFoodListingStatus(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        $request->validate([
            'status' => 'required|in:active,inactive,expired'
        ]);
        
        try {
            $listing = FoodListing::with('establishment')->find($id);
            
            if (!$listing) {
                return response()->json(['success' => false, 'message' => 'Food listing not found.'], 404);
            }
            
            $oldStatus = $listing->status;
            $listing->status = $request->status;
            $listing->save();
            
            $action = match($request->status) {
                'active' => 'activated',
                'inactive' => 'disabled',
                'expired' => 'marked as expired',
                default => 'updated'
            };
            
            // Log the action in System Logs
            $establishmentName = $listing->establishment 
                ? ($listing->establishment->business_name ?? 'Unknown') 
                : 'Unknown';
            
            SystemLog::log(
                'food_listing_management',
                'food_listing_' . $request->status,
                "Food listing {$action} by admin: {$listing->name} (ID: {$listing->id}) from {$establishmentName}",
                $request->status === 'inactive' ? 'warning' : 'info',
                'success',
                [
                    'food_listing_id' => $listing->id,
                    'food_listing_name' => $listing->name,
                    'establishment_id' => $listing->establishment_id,
                    'establishment_name' => $establishmentName,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'action' => $action,
                    'admin_id' => session('user_id'),
                    'admin_email' => session('user_email'),
                ]
            );
            
            // Send notification to establishment when item is disabled
            if ($request->status === 'inactive' && $listing->establishment_id) {
                try {
                    Notification::createNotification(
                        'establishment',
                        $listing->establishment_id,
                        'food_listing_disabled',
                        'Food Item Disabled',
                        "Your food item '{$listing->name}' has been disabled by an administrator. It will no longer be visible to customers.",
                        [
                            'data' => [
                                'food_listing_id' => $listing->id,
                                'food_listing_name' => $listing->name,
                                'reason' => 'Disabled by administrator',
                            ]
                        ]
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send notification to establishment about disabled food listing: ' . $e->getMessage(), [
                        'food_listing_id' => $listing->id,
                        'establishment_id' => $listing->establishment_id
                    ]);
                    // Don't fail the request if notification fails
                }
            }
            
            // Send notification to establishment when item is enabled
            if ($request->status === 'active' && $oldStatus === 'inactive' && $listing->establishment_id) {
                try {
                    Notification::createNotification(
                        'establishment',
                        $listing->establishment_id,
                        'food_listing_enabled',
                        'Food Item Enabled',
                        "Your food item '{$listing->name}' has been enabled and is now visible to customers.",
                        [
                            'data' => [
                                'food_listing_id' => $listing->id,
                                'food_listing_name' => $listing->name,
                            ]
                        ]
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send notification to establishment about enabled food listing: ' . $e->getMessage(), [
                        'food_listing_id' => $listing->id,
                        'establishment_id' => $listing->establishment_id
                    ]);
                    // Don't fail the request if notification fails
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Food listing {$action} successfully."
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating food listing status', [
                'food_listing_id' => $id,
                'status' => $request->status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update food listing status.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while updating the food listing status.'
            ], 500);
        }
    }
    
    /**
     * Admin - Delete Food Listing
     */
    public function deleteFoodListing($id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        try {
            $listing = FoodListing::with('establishment')->find($id);
            
            if (!$listing) {
                return response()->json(['success' => false, 'message' => 'Food listing not found.'], 404);
            }
            
            // Store information before deletion
            $listingName = $listing->name;
            $listingId = $listing->id;
            $establishmentId = $listing->establishment_id;
            $establishmentName = $listing->establishment 
                ? ($listing->establishment->business_name ?? 'Unknown') 
                : 'Unknown';
            
            // Delete the listing
            $listing->delete();
            
            // Log the action in System Logs
            SystemLog::log(
                'food_listing_management',
                'food_listing_deleted',
                "Food listing deleted by admin: {$listingName} (ID: {$listingId}) from {$establishmentName}",
                'warning',
                'success',
                [
                    'food_listing_id' => $listingId,
                    'food_listing_name' => $listingName,
                    'establishment_id' => $establishmentId,
                    'establishment_name' => $establishmentName,
                    'admin_id' => session('user_id'),
                    'admin_email' => session('user_email'),
                ]
            );
            
            // Send notification to establishment
            if ($establishmentId) {
                try {
                    Notification::createNotification(
                        'establishment',
                        $establishmentId,
                        'food_listing_deleted',
                        'Food Item Deleted',
                        "Your food item '{$listingName}' has been permanently deleted by an administrator. This action cannot be undone.",
                        [
                            'data' => [
                                'food_listing_id' => $listingId,
                                'food_listing_name' => $listingName,
                                'reason' => 'Deleted by administrator',
                            ]
                        ]
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send notification to establishment about deleted food listing: ' . $e->getMessage(), [
                        'food_listing_id' => $listingId,
                        'establishment_id' => $establishmentId
                    ]);
                    // Don't fail the request if notification fails
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Food listing deleted successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting food listing', [
                'food_listing_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete food listing.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while deleting the food listing.'
            ], 500);
        }
    }

    /**
     * Admin - Order Management
     */
    public function adminOrders(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        
        // Get filter parameters
        $searchQuery = $request->get('search', '');
        $statusFilter = $request->get('status', 'all');
        $dateFilter = $request->get('date', 'all');
        
        // Optimized query with eager loading of all relationships
        $ordersQuery = Order::with([
            'consumer' => function($query) {
                $query->select('consumer_id', 'fname', 'lname', 'email', 'phone_no');
            },
            'establishment' => function($query) {
                $query->select('establishment_id', 'business_name', 'email', 'phone_no');
            },
            'foodListing' => function($query) {
                $query->select('id', 'name', 'category', 'image_path');
            }
        ]);
        
        // Apply search filter
        if ($searchQuery) {
            $ordersQuery->where(function($query) use ($searchQuery) {
                $query->where('order_number', 'like', "%{$searchQuery}%")
                      ->orWhere('customer_name', 'like', "%{$searchQuery}%")
                      ->orWhere('customer_phone', 'like', "%{$searchQuery}%")
                      ->orWhereHas('consumer', function($q) use ($searchQuery) {
                          $q->where('fname', 'like', "%{$searchQuery}%")
                            ->orWhere('lname', 'like', "%{$searchQuery}%")
                            ->orWhere('email', 'like', "%{$searchQuery}%");
                      })
                      ->orWhereHas('establishment', function($q) use ($searchQuery) {
                          $q->where('business_name', 'like', "%{$searchQuery}%")
                            ->orWhere('email', 'like', "%{$searchQuery}%");
                      })
                      ->orWhereHas('foodListing', function($q) use ($searchQuery) {
                          $q->where('name', 'like', "%{$searchQuery}%");
                      });
            });
        }
        
        // Apply status filter
        if ($statusFilter !== 'all') {
            $ordersQuery->where('status', $statusFilter);
        }
        
        // Apply date filter
        if ($dateFilter !== 'all') {
            $today = now()->toDateString();
            if ($dateFilter === 'today') {
                $ordersQuery->whereDate('created_at', $today);
            } elseif ($dateFilter === 'week') {
                $ordersQuery->where('created_at', '>=', now()->subWeek());
            } elseif ($dateFilter === 'month') {
                $ordersQuery->where('created_at', '>=', now()->subMonth());
            }
        }
        
        // Get orders ordered by creation date (newest first)
        $orders = $ordersQuery->orderBy('created_at', 'desc')->get();
        
        // Format orders data
        $formattedOrders = $orders->map(function($order) {
            $consumer = $order->consumer;
            $establishment = $order->establishment;
            $foodListing = $order->foodListing;
            
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'effective_status' => $order->effective_status,
                'quantity' => $order->quantity,
                'unit_price' => $order->unit_price,
                'total_price' => $order->total_price,
                'delivery_method' => $order->delivery_method,
                'payment_method' => $order->payment_method,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'delivery_address' => $order->delivery_address,
                'pickup_start_time' => $order->pickup_start_time,
                'pickup_end_time' => $order->pickup_end_time,
                'accepted_at' => $order->accepted_at,
                'completed_at' => $order->completed_at,
                'cancelled_at' => $order->cancelled_at,
                'cancellation_reason' => $order->cancellation_reason,
                'created_at' => $order->created_at,
                'consumer' => $consumer ? [
                    'id' => $consumer->consumer_id,
                    'name' => $consumer->fname . ' ' . $consumer->lname,
                    'email' => $consumer->email,
                    'phone' => $consumer->phone_no,
                ] : null,
                'establishment' => $establishment ? [
                    'id' => $establishment->establishment_id,
                    'name' => $establishment->business_name,
                    'email' => $establishment->email,
                    'phone' => $establishment->phone_no,
                ] : null,
                'food_listing' => $foodListing ? [
                    'id' => $foodListing->id,
                    'name' => $foodListing->name,
                    'category' => $foodListing->category,
                    'image_path' => $foodListing->image_path,
                ] : null,
            ];
        });
        
        // Statistics
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'accepted' => Order::where('status', 'accepted')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'today' => Order::whereDate('created_at', now()->toDateString())->count(),
        ];
        
        return view('admin.orders', compact(
            'user',
            'formattedOrders',
            'stats',
            'searchQuery',
            'statusFilter',
            'dateFilter'
        ));
    }
    
    /**
     * Admin - Force Cancel Order
     */
    public function forceCancelOrder(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);
        
        try {
            $order = Order::with(['consumer', 'establishment', 'foodListing'])->find($id);
            
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
            }
            
            if ($order->status === 'cancelled') {
                return response()->json(['success' => false, 'message' => 'Order is already cancelled.'], 400);
            }
            
            if ($order->status === 'completed') {
                return response()->json(['success' => false, 'message' => 'Cannot cancel a completed order.'], 400);
            }
            
            $oldStatus = $order->status;
            $cancellationReason = $request->reason 
                ? 'Admin Force Cancel: ' . $request->reason 
                : 'Admin Force Cancel';
            
            DB::beginTransaction();
            try {
                // Only restore stock if it was actually deducted (i.e., order was accepted)
                // If order is still pending, no stock was deducted, so nothing to restore
                if ($order->stock_deducted) {
                    $stockService = new \App\Services\StockService();
                    $stockResult = $stockService->restoreStock($order, $cancellationReason);
                    
                    // Log the result but don't fail if stock wasn't deducted
                    if (!$stockResult['success'] && strpos($stockResult['message'] ?? '', 'already restored') === false) {
                        // Only throw if it's a real error, not if stock was never deducted
                        if (strpos($stockResult['message'] ?? '', 'never deducted') === false) {
                            throw new \Exception($stockResult['message']);
                        }
                    }
                }
                
                $order->status = 'cancelled';
                $order->cancelled_at = now();
                $order->cancellation_reason = $cancellationReason;
                $order->save();
                
                // Log the action in System Logs
                $consumerName = $order->consumer 
                    ? ($order->consumer->fname . ' ' . $order->consumer->lname) 
                    : ($order->customer_name ?? 'Unknown');
                $establishmentName = $order->establishment 
                    ? ($order->establishment->business_name ?? 'Unknown') 
                    : 'Unknown';
                
                SystemLog::log(
                    'order_management',
                    'order_force_cancelled',
                    "Order force cancelled by admin: Order #{$order->order_number} (ID: {$order->id}) - Consumer: {$consumerName}, Establishment: {$establishmentName}",
                    'warning',
                    'success',
                    [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'consumer_id' => $order->consumer_id,
                        'consumer_name' => $consumerName,
                        'establishment_id' => $order->establishment_id,
                        'establishment_name' => $establishmentName,
                        'old_status' => $oldStatus,
                        'new_status' => 'cancelled',
                        'cancellation_reason' => $cancellationReason,
                        'admin_id' => session('user_id'),
                        'admin_email' => session('user_email'),
                    ]
                );
                
                // Send notifications to consumer and establishment
                try {
                    if ($order->consumer_id) {
                        Notification::createNotification(
                            'consumer',
                            $order->consumer_id,
                            'order_cancelled',
                            'Order Cancelled',
                            "Your order #{$order->order_number} has been cancelled by an administrator." . ($request->reason ? " Reason: {$request->reason}" : ''),
                            [
                                'order_id' => $order->id,
                                'data' => [
                                    'order_number' => $order->order_number,
                                    'cancellation_reason' => $cancellationReason,
                                ]
                            ]
                        );
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send notification to consumer about cancelled order: ' . $e->getMessage(), [
                        'order_id' => $order->id,
                        'consumer_id' => $order->consumer_id
                    ]);
                }
                
                try {
                    if ($order->establishment_id) {
                        Notification::createNotification(
                            'establishment',
                            $order->establishment_id,
                            'order_cancelled',
                            'Order Cancelled',
                            "Order #{$order->order_number} has been cancelled by an administrator." . ($request->reason ? " Reason: {$request->reason}" : ''),
                            [
                                'order_id' => $order->id,
                                'data' => [
                                    'order_number' => $order->order_number,
                                    'cancellation_reason' => $cancellationReason,
                                ]
                            ]
                        );
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send notification to establishment about cancelled order: ' . $e->getMessage(), [
                        'order_id' => $order->id,
                        'establishment_id' => $order->establishment_id
                    ]);
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled and stock restored successfully.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error force cancelling order', [
                    'order_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel order: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Error in forceCancelOrder', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while cancelling the order.'
            ], 500);
        }
    }
    
    /**
     * Admin - Resolve Dispute
     */
    public function resolveDispute(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        $request->validate([
            'resolution' => 'required|string|in:refund,complete,cancel',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        try {
            $order = Order::find($id);
            
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
            }
            
            $resolution = $request->resolution;
            $notes = $request->notes ?? '';
            
            if ($resolution === 'refund') {
                // Mark as cancelled with refund note
                $order->status = 'cancelled';
                $order->cancelled_at = now();
                $order->cancellation_reason = 'Dispute Resolved - Refund Issued. Admin Notes: ' . $notes;
            } elseif ($resolution === 'complete') {
                // Force complete the order
                $order->status = 'completed';
                $order->completed_at = now();
                if ($notes) {
                    $order->cancellation_reason = 'Dispute Resolved - Order Completed. Admin Notes: ' . $notes;
                }
            } elseif ($resolution === 'cancel') {
                // Cancel the order
                $order->status = 'cancelled';
                $order->cancelled_at = now();
                $order->cancellation_reason = 'Dispute Resolved - Order Cancelled. Admin Notes: ' . $notes;
            }
            
            $order->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Dispute resolved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve dispute.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin - Donation Hub
     */
    public function adminDonations(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        
        // Get filter parameters
        $searchQuery = $request->get('search', '');
        $typeFilter = $request->get('type', 'all'); // 'all', 'donations', 'requests'
        $statusFilter = $request->get('status', 'all');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');
        $perPage = $request->get('per_page', 20);
        
        // Initialize collections
        $donations = collect();
        $donationRequests = collect();
        
        // Fetch Donations with relationships
        if ($typeFilter === 'all' || $typeFilter === 'donations') {
            $donationsQuery = Donation::with([
                'establishment' => function($query) {
                    $query->select('establishment_id', 'business_name', 'email', 'phone_no');
                },
                'foodbank' => function($query) {
                    $query->select('foodbank_id', 'organization_name', 'email', 'phone_no');
                },
                'donationRequest' => function($query) {
                    $query->select('donation_request_id', 'item_name', 'status');
                }
            ]);
            
            // Apply search filter
            if ($searchQuery) {
                $donationsQuery->where(function($query) use ($searchQuery) {
                    $query->where('donation_number', 'like', "%{$searchQuery}%")
                          ->orWhere('item_name', 'like', "%{$searchQuery}%")
                          ->orWhereHas('establishment', function($q) use ($searchQuery) {
                              $q->where('business_name', 'like', "%{$searchQuery}%");
                          })
                          ->orWhereHas('foodbank', function($q) use ($searchQuery) {
                              $q->where('organization_name', 'like', "%{$searchQuery}%");
                          });
                });
            }
            
            // Apply status filter
            if ($statusFilter !== 'all') {
                $donationsQuery->where('status', $statusFilter);
            }
            
            // Apply date filters
            if ($dateFrom) {
                $donationsQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $donationsQuery->whereDate('created_at', '<=', $dateTo);
            }
            
            $donations = $donationsQuery->orderBy('created_at', 'desc')->get();
        }
        
        // Fetch Donation Requests with relationships
        if ($typeFilter === 'all' || $typeFilter === 'requests') {
            $requestsQuery = DonationRequest::with([
                'foodbank' => function($query) {
                    $query->select('foodbank_id', 'organization_name', 'email', 'phone_no');
                }
            ]);
            
            // Apply search filter
            if ($searchQuery) {
                $requestsQuery->where(function($query) use ($searchQuery) {
                    $query->where('item_name', 'like', "%{$searchQuery}%")
                          ->orWhere('contact_name', 'like', "%{$searchQuery}%")
                          ->orWhere('email', 'like', "%{$searchQuery}%")
                          ->orWhereHas('foodbank', function($q) use ($searchQuery) {
                              $q->where('organization_name', 'like', "%{$searchQuery}%");
                          });
                });
            }
            
            // Apply status filter
            if ($statusFilter !== 'all') {
                $requestsQuery->where('status', $statusFilter);
            }
            
            // Apply date filters
            if ($dateFrom) {
                $requestsQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $requestsQuery->whereDate('created_at', '<=', $dateTo);
            }
            
            $donationRequests = $requestsQuery->orderBy('created_at', 'desc')->get();
        }
        
        // Format donations data
        $formattedDonations = $donations->map(function($donation) {
            $establishment = $donation->establishment;
            $foodbank = $donation->foodbank;
            $donationRequest = $donation->donationRequest;
            
            return [
                'id' => $donation->donation_id,
                'donation_number' => $donation->donation_number,
                'item_name' => $donation->item_name,
                'item_category' => $donation->item_category,
                'quantity' => $donation->quantity,
                'unit' => $donation->unit,
                'description' => $donation->description,
                'expiry_date' => $donation->expiry_date ? ($donation->expiry_date instanceof \Carbon\Carbon ? $donation->expiry_date->format('Y-m-d') : $donation->expiry_date) : null,
                'status' => $donation->status,
                'pickup_method' => $donation->pickup_method,
                'scheduled_date' => $donation->scheduled_date ? ($donation->scheduled_date instanceof \Carbon\Carbon ? $donation->scheduled_date->format('Y-m-d') : $donation->scheduled_date) : null,
                'scheduled_time' => $donation->scheduled_time ? ($donation->scheduled_time instanceof \Carbon\Carbon ? $donation->scheduled_time->format('H:i:s') : $donation->scheduled_time) : null,
                'collected_at' => $donation->collected_at ? ($donation->collected_at instanceof \Carbon\Carbon ? $donation->collected_at->toDateTimeString() : $donation->collected_at) : null,
                'is_urgent' => $donation->is_urgent ?? false,
                'created_at' => $donation->created_at ? ($donation->created_at instanceof \Carbon\Carbon ? $donation->created_at->toDateTimeString() : $donation->created_at) : null,
                'establishment' => ($establishment && $establishment instanceof \Illuminate\Database\Eloquent\Model) ? [
                    'id' => $establishment->establishment_id ?? null,
                    'name' => $establishment->business_name ?? null,
                    'email' => $establishment->email ?? null,
                    'phone' => $establishment->phone_no ?? null,
                ] : null,
                'foodbank' => ($foodbank && $foodbank instanceof \Illuminate\Database\Eloquent\Model) ? [
                    'id' => $foodbank->foodbank_id ?? null,
                    'name' => $foodbank->organization_name ?? null,
                    'email' => $foodbank->email ?? null,
                    'phone' => $foodbank->phone_no ?? null,
                ] : null,
                'donation_request' => ($donationRequest && $donationRequest instanceof \Illuminate\Database\Eloquent\Model) ? [
                    'id' => $donationRequest->donation_request_id ?? null,
                    'item_name' => $donationRequest->item_name ?? null,
                    'status' => $donationRequest->status ?? null,
                ] : null,
            ];
        });
        
        // Format donation requests data
        $formattedRequests = $donationRequests->map(function($request) {
            $foodbank = $request->foodbank;
            
            return [
                'id' => $request->donation_request_id,
                'item_name' => $request->item_name,
                'quantity' => $request->quantity,
                'category' => $request->category,
                'description' => $request->description,
                'distribution_zone' => $request->distribution_zone,
                'dropoff_date' => $request->dropoff_date ? ($request->dropoff_date instanceof \Carbon\Carbon ? $request->dropoff_date->format('Y-m-d') : $request->dropoff_date) : null,
                'time_option' => $request->time_option,
                'start_time' => $request->start_time ? ($request->start_time instanceof \Carbon\Carbon ? $request->start_time->toDateTimeString() : $request->start_time) : null,
                'end_time' => $request->end_time ? ($request->end_time instanceof \Carbon\Carbon ? $request->end_time->toDateTimeString() : $request->end_time) : null,
                'address' => $request->address,
                'contact_name' => $request->contact_name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'status' => $request->status,
                'matches' => $request->matches ?? 0,
                'created_at' => $request->created_at ? ($request->created_at instanceof \Carbon\Carbon ? $request->created_at->toDateTimeString() : $request->created_at) : null,
                'foodbank' => ($foodbank && $foodbank instanceof \Illuminate\Database\Eloquent\Model) ? [
                    'id' => $foodbank->foodbank_id ?? null,
                    'name' => $foodbank->organization_name ?? null,
                    'email' => $foodbank->email ?? null,
                    'phone' => $foodbank->phone_no ?? null,
                ] : null,
            ];
        });
        
        // Combine and paginate
        $allRecords = $formattedDonations->map(function($item) {
            $item['record_type'] = 'donation';
            return $item;
        })->concat($formattedRequests->map(function($item) {
            $item['record_type'] = 'request';
            return $item;
        }))->sortByDesc('created_at')->values();
        
        // Manual pagination
        $currentPage = $request->get('page', 1);
        $total = $allRecords->count();
        $items = $allRecords->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        // Statistics
        $stats = [
            'total_donations' => Donation::count(),
            'total_requests' => DonationRequest::count(),
            'pending_donations' => Donation::where('status', 'pending_pickup')->count(),
            'collected_donations' => Donation::where('status', 'collected')->count(),
            'active_requests' => DonationRequest::whereIn('status', [
                DonationRequestService::STATUS_PENDING,
                DonationRequestService::STATUS_ACCEPTED
            ])->count(),
            'completed_requests' => DonationRequest::where('status', DonationRequestService::STATUS_COMPLETED)->count(),
        ];
        
        return view('admin.donations', compact(
            'user',
            'items',
            'stats',
            'searchQuery',
            'typeFilter',
            'statusFilter',
            'dateFrom',
            'dateTo',
            'perPage',
            'currentPage',
            'total'
        ));
    }
    
    /**
     * Admin - Export Donations to CSV
     */
    public function exportDonationsToCsv(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        // Get filter parameters (same as main view)
        $searchQuery = $request->get('search', '');
        $typeFilter = $request->get('type', 'all');
        $statusFilter = $request->get('status', 'all');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');
        
        $donations = collect();
        $donationRequests = collect();
        
        // Fetch Donations
        if ($typeFilter === 'all' || $typeFilter === 'donations') {
            $donationsQuery = Donation::with(['establishment', 'foodbank', 'donationRequest']);
            
            if ($searchQuery) {
                $donationsQuery->where(function($query) use ($searchQuery) {
                    $query->where('donation_number', 'like', "%{$searchQuery}%")
                          ->orWhere('item_name', 'like', "%{$searchQuery}%")
                          ->orWhereHas('establishment', function($q) use ($searchQuery) {
                              $q->where('business_name', 'like', "%{$searchQuery}%");
                          })
                          ->orWhereHas('foodbank', function($q) use ($searchQuery) {
                              $q->where('organization_name', 'like', "%{$searchQuery}%");
                          });
                });
            }
            
            if ($statusFilter !== 'all') {
                $donationsQuery->where('status', $statusFilter);
            }
            
            if ($dateFrom) {
                $donationsQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $donationsQuery->whereDate('created_at', '<=', $dateTo);
            }
            
            $donations = $donationsQuery->orderBy('created_at', 'desc')->get();
        }
        
        // Fetch Donation Requests
        if ($typeFilter === 'all' || $typeFilter === 'requests') {
            $requestsQuery = DonationRequest::with(['foodbank']);
            
            if ($searchQuery) {
                $requestsQuery->where(function($query) use ($searchQuery) {
                    $query->where('item_name', 'like', "%{$searchQuery}%")
                          ->orWhere('contact_name', 'like', "%{$searchQuery}%")
                          ->orWhereHas('foodbank', function($q) use ($searchQuery) {
                              $q->where('organization_name', 'like', "%{$searchQuery}%");
                          });
                });
            }
            
            if ($statusFilter !== 'all') {
                $requestsQuery->where('status', $statusFilter);
            }
            
            if ($dateFrom) {
                $requestsQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $requestsQuery->whereDate('created_at', '<=', $dateTo);
            }
            
            $donationRequests = $requestsQuery->orderBy('created_at', 'desc')->get();
        }
        
        // Generate CSV
        $filename = 'donations_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($donations, $donationRequests) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Type',
                'ID/Number',
                'Item Name',
                'Category',
                'Quantity',
                'Unit',
                'Status',
                'Establishment',
                'Food Bank',
                'Date Created',
                'Scheduled Date',
                'Collected At',
                'Description'
            ]);
            
            // Donations
            foreach ($donations as $donation) {
                fputcsv($file, [
                    'Donation',
                    $donation->donation_number,
                    $donation->item_name,
                    $donation->item_category,
                    $donation->quantity,
                    $donation->unit,
                    $donation->status,
                    $donation->establishment ? $donation->establishment->business_name : 'N/A',
                    $donation->foodbank ? $donation->foodbank->organization_name : 'N/A',
                    $donation->created_at->format('Y-m-d H:i:s'),
                    $donation->scheduled_date ? $donation->scheduled_date->format('Y-m-d') : 'N/A',
                    $donation->collected_at ? $donation->collected_at->format('Y-m-d H:i:s') : 'N/A',
                    $donation->description ?? ''
                ]);
            }
            
            // Donation Requests
            foreach ($donationRequests as $request) {
                fputcsv($file, [
                    'Request',
                    $request->donation_request_id,
                    $request->item_name,
                    $request->category,
                    $request->quantity,
                    'pcs',
                    $request->status,
                    'N/A',
                    $request->foodbank ? $request->foodbank->organization_name : 'N/A',
                    $request->created_at->format('Y-m-d H:i:s'),
                    $request->dropoff_date ? $request->dropoff_date->format('Y-m-d') : 'N/A',
                    'N/A',
                    $request->description ?? ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Admin - Announcement Management
     */
    public function adminAnnouncements(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        
        // Get filter parameters
        $searchQuery = $request->get('search', '');
        $statusFilter = $request->get('status', 'all');
        $audienceFilter = $request->get('audience', 'all');
        
        // Query announcements
        $announcementsQuery = Announcement::query();
        
        // Apply search filter
        if ($searchQuery) {
            $announcementsQuery->where(function($query) use ($searchQuery) {
                $query->where('title', 'like', "%{$searchQuery}%")
                      ->orWhere('message', 'like', "%{$searchQuery}%");
            });
        }
        
        // Apply status filter
        if ($statusFilter !== 'all') {
            $announcementsQuery->where('status', $statusFilter);
        }
        
        // Apply audience filter
        if ($audienceFilter !== 'all') {
            $announcementsQuery->where('target_audience', $audienceFilter);
        }
        
        // Get announcements ordered by creation date (newest first)
        $announcements = $announcementsQuery->orderBy('created_at', 'desc')->get();
        
        // Statistics
        $stats = [
            'total' => Announcement::count(),
            'active' => Announcement::where('status', 'active')->count(),
            'inactive' => Announcement::where('status', 'inactive')->count(),
            'archived' => Announcement::where('status', 'archived')->count(),
        ];
        
        return view('admin.announcements', compact(
            'user',
            'announcements',
            'stats',
            'searchQuery',
            'statusFilter',
            'audienceFilter'
        ));
    }
    
    /**
     * Admin - Store Announcement
     */
    public function storeAnnouncement(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target_audience' => 'required|in:all,consumer,establishment,foodbank',
            'status' => 'required|in:active,inactive,archived',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);
        
        try {
            $announcement = Announcement::create([
                'title' => $request->title,
                'message' => $request->message,
                'target_audience' => $request->target_audience,
                'status' => $request->status,
                'published_at' => $request->published_at ? \Carbon\Carbon::parse($request->published_at) : now(),
                'expires_at' => $request->expires_at ? \Carbon\Carbon::parse($request->expires_at) : null,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Announcement created successfully.',
                'announcement' => $announcement
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
     * Admin - Update Announcement
     */
    public function updateAnnouncement(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target_audience' => 'required|in:all,consumer,establishment,foodbank',
            'status' => 'required|in:active,inactive,archived',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);
        
        try {
            $announcement = Announcement::find($id);
            
            if (!$announcement) {
                return response()->json(['success' => false, 'message' => 'Announcement not found.'], 404);
            }
            
            $announcement->update([
                'title' => $request->title,
                'message' => $request->message,
                'target_audience' => $request->target_audience,
                'status' => $request->status,
                'published_at' => $request->published_at ? \Carbon\Carbon::parse($request->published_at) : null,
                'expires_at' => $request->expires_at ? \Carbon\Carbon::parse($request->expires_at) : null,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully.',
                'announcement' => $announcement
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
     * Admin - Delete Announcement
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

    /**
     * Admin - Reports & Analytics
     */
    public function adminReports()
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        return view('admin.reports', compact('user'));
    }

    /**
     * Admin - SavEats Company Earnings
     */
    public function adminEarnings(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        
        // Build query for completed orders with platform fees
        $query = Order::where('status', 'completed')
            ->with(['establishment', 'foodListing', 'consumer']);
        
        // Apply filters
        if ($request->filled('establishment_id')) {
            $query->where('establishment_id', $request->establishment_id);
        }
        
        if ($request->filled('date_from')) {
            $query->where('completed_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('completed_at', '<=', $request->date_to . ' 23:59:59');
        }
        
        // Get all orders for statistics
        $allOrders = Order::where('status', 'completed')->get();
        
        // Backfill platform fees for orders without them
        $ordersWithoutFee = $allOrders->filter(function($order) {
            return is_null($order->platform_fee) || $order->platform_fee == 0;
        });
        
        foreach ($ordersWithoutFee as $order) {
            $platformFee = round($order->total_price * 0.05, 2);
            $netEarnings = round($order->total_price - $platformFee, 2);
            $order->platform_fee = $platformFee;
            $order->net_earnings = $netEarnings;
            $order->save();
        }
        
        // Get filtered orders with pagination
        $orders = $query->orderBy('completed_at', 'desc')
            ->paginate(20)
            ->through(function ($order) {
                $platformFee = $order->platform_fee ?? round($order->total_price * 0.05, 2);
                $netEarnings = $order->net_earnings ?? round($order->total_price - $platformFee, 2);
                
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'establishment_name' => $order->establishment->business_name ?? 'Unknown',
                    'establishment_id' => $order->establishment_id,
                    'item_name' => $order->foodListing->name ?? 'Unknown Item',
                    'quantity' => $order->quantity,
                    'total_price' => (float) $order->total_price,
                    'platform_fee' => (float) $platformFee,
                    'net_earnings' => (float) $netEarnings,
                    'completed_at' => $order->completed_at ? $order->completed_at->format('Y-m-d H:i:s') : null,
                    'completed_at_display' => $order->completed_at ? $order->completed_at->format('M d, Y g:i A') : 'N/A',
                ];
            });
        
        // Calculate totals
        $totalGrossRevenue = $allOrders->sum('total_price');
        $totalPlatformFees = $allOrders->sum(function($order) {
            return $order->platform_fee ?? round($order->total_price * 0.05, 2);
        });
        $totalNetEarnings = $allOrders->sum(function($order) {
            return $order->net_earnings ?? round($order->total_price * 0.95, 2);
        });
        
        // Get unique establishments for filter
        $establishments = Establishment::whereIn('establishment_id', $allOrders->pluck('establishment_id')->unique())
            ->get(['establishment_id', 'business_name'])
            ->map(function ($est) {
                return ['id' => $est->establishment_id, 'name' => $est->business_name];
            });
        
        // Calculate daily earnings (last 30 days)
        $dailyEarnings = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayFees = Order::where('status', 'completed')
                ->whereDate('completed_at', $date->toDateString())
                ->get()
                ->sum(function($order) {
                    return $order->platform_fee ?? round($order->total_price * 0.05, 2);
                });
            
            $dailyEarnings[] = [
                'label' => $date->format('M d'),
                'value' => (float) $dayFees
            ];
        }
        
        // Calculate monthly earnings (last 12 months)
        $monthlyEarnings = [];
        $monthLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthFees = Order::where('status', 'completed')
                ->whereYear('completed_at', $date->year)
                ->whereMonth('completed_at', $date->month)
                ->get()
                ->sum(function($order) {
                    return $order->platform_fee ?? round($order->total_price * 0.05, 2);
                });
            
            $monthlyEarnings[] = [
                'label' => $monthLabels[$date->month - 1],
                'value' => (float) $monthFees
            ];
        }
        
        return view('admin.earnings', compact(
            'user',
            'orders',
            'totalGrossRevenue',
            'totalPlatformFees',
            'totalNetEarnings',
            'establishments',
            'dailyEarnings',
            'monthlyEarnings'
        ));
    }

    /**
     * Export Admin Earnings
     */
    public function exportAdminEarnings(Request $request, $type)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }

        // Build query with same filters as adminEarnings
        $query = Order::where('status', 'completed')
            ->with(['establishment', 'foodListing']);

        if ($request->filled('establishment_id')) {
            $query->where('establishment_id', $request->establishment_id);
        }

        if ($request->filled('date_from')) {
            $query->where('completed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('completed_at', '<=', $request->date_to . ' 23:59:59');
        }

        $orders = $query->orderBy('completed_at', 'desc')->get();

        switch ($type) {
            case 'csv':
                return $this->exportAdminEarningsToCsv($orders);
            case 'excel':
                return $this->exportAdminEarningsToExcel($orders);
            case 'pdf':
                return $this->exportAdminEarningsToPdf($orders);
            default:
                return redirect()->back()->with('error', 'Invalid export type.');
        }
    }

    /**
     * Export Admin Earnings to CSV
     */
    private function exportAdminEarningsToCsv($orders)
    {
        $filename = 'saveats_earnings_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Order Number',
                'Establishment',
                'Item Name',
                'Quantity',
                'Gross Amount',
                'Platform Fee (5%)',
                'Net to Establishment',
                'Date Completed'
            ]);

            // Data rows
            foreach ($orders as $order) {
                $platformFee = $order->platform_fee ?? round($order->total_price * 0.05, 2);
                $netEarnings = $order->net_earnings ?? round($order->total_price - $platformFee, 2);
                
                fputcsv($file, [
                    $order->order_number,
                    $order->establishment->business_name ?? 'Unknown',
                    $order->foodListing->name ?? 'Unknown Item',
                    $order->quantity,
                    $order->total_price,
                    $platformFee,
                    $netEarnings,
                    $order->completed_at ? $order->completed_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Admin Earnings to Excel
     */
    private function exportAdminEarningsToExcel($orders)
    {
        $filename = 'saveats_earnings_' . date('Y-m-d_His') . '.xlsx';
        
        $data = [];
        
        // Headers
        $data[] = [
            'Order Number',
            'Establishment',
            'Item Name',
            'Quantity',
            'Gross Amount',
            'Platform Fee (5%)',
            'Net to Establishment',
            'Date Completed'
        ];
        
        // Data rows
        foreach ($orders as $order) {
            $platformFee = $order->platform_fee ?? round($order->total_price * 0.05, 2);
            $netEarnings = $order->net_earnings ?? round($order->total_price - $platformFee, 2);
            
            $data[] = [
                $order->order_number,
                $order->establishment->business_name ?? 'Unknown',
                $order->foodListing->name ?? 'Unknown Item',
                $order->quantity,
                $order->total_price,
                $platformFee,
                $netEarnings,
                $order->completed_at ? $order->completed_at->format('Y-m-d H:i:s') : 'N/A'
            ];
        }
        
        return Excel::create($filename, function($excel) use ($data) {
            $excel->sheet('SavEats Earnings', function($sheet) use ($data) {
                $sheet->fromArray($data, null, 'A1', false, false);
                
                // Style the header row
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                    $row->setBackground('#ef4444');
                    $row->setFontColor('#ffffff');
                });
                
                // Auto-size columns
                foreach(range('A', 'H') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            });
        })->export('xlsx');
    }

    /**
     * Export Admin Earnings to PDF
     */
    private function exportAdminEarningsToPdf($orders)
    {
        $data = $orders->map(function ($order) {
            $platformFee = $order->platform_fee ?? round($order->total_price * 0.05, 2);
            $netEarnings = $order->net_earnings ?? round($order->total_price - $platformFee, 2);
            
            return [
                'order_number' => $order->order_number,
                'establishment_name' => $order->establishment->business_name ?? 'Unknown',
                'item_name' => $order->foodListing->name ?? 'Unknown Item',
                'quantity' => $order->quantity,
                'total_price' => (float) $order->total_price,
                'platform_fee' => (float) $platformFee,
                'net_earnings' => (float) $netEarnings,
                'completed_at' => $order->completed_at ? $order->completed_at->format('F d, Y H:i') : 'N/A',
            ];
        })->toArray();

        $totalGross = $orders->sum('total_price');
        $totalFees = $orders->sum(function($order) {
            return $order->platform_fee ?? round($order->total_price * 0.05, 2);
        });
        $totalNet = $orders->sum(function($order) {
            return $order->net_earnings ?? round($order->total_price * 0.95, 2);
        });

        $filename = 'saveats_earnings_' . date('Y-m-d_His') . '.pdf';
        
        $pdf = Pdf::loadView('admin.earnings-pdf', [
            'data' => $data,
            'totalGross' => $totalGross,
            'totalFees' => $totalFees,
            'totalNet' => $totalNet,
            'exportDate' => now()->format('F d, Y'),
            'totalTransactions' => count($data)
        ]);
        
        return $pdf->download($filename);
    }


    /**
     * Admin - Review Management
     */
    public function adminReviews(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        
        // Get filter parameters
        $searchQuery = $request->get('search', '');
        $ratingFilter = $request->get('rating', 'all');
        $flaggedFilter = $request->get('flagged', 'all');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');
        $perPage = $request->get('per_page', 20);
        
        // Build query
        $query = Review::with([
            'consumer' => function($query) {
                $query->select('consumer_id', 'fname', 'lname', 'email');
            },
            'establishment' => function($query) {
                $query->select('establishment_id', 'business_name', 'email');
            },
            'foodListing' => function($query) {
                $query->select('id', 'name');
            }
        ]);
        
        // Apply search filter
        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('description', 'like', "%{$searchQuery}%")
                  ->orWhereHas('consumer', function($q) use ($searchQuery) {
                      $q->where('fname', 'like', "%{$searchQuery}%")
                        ->orWhere('lname', 'like', "%{$searchQuery}%")
                        ->orWhere('email', 'like', "%{$searchQuery}%");
                  })
                  ->orWhereHas('establishment', function($q) use ($searchQuery) {
                      $q->where('business_name', 'like', "%{$searchQuery}%");
                  })
                  ->orWhereHas('foodListing', function($q) use ($searchQuery) {
                      $q->where('name', 'like', "%{$searchQuery}%");
                  });
            });
        }
        
        // Apply rating filter
        if ($ratingFilter !== 'all') {
            $query->where('rating', $ratingFilter);
        }
        
        // Apply flagged filter
        if ($flaggedFilter === 'yes') {
            $query->where('flagged', true);
        } elseif ($flaggedFilter === 'no') {
            $query->where('flagged', false);
        }
        
        // Apply date filters
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        
        // Get paginated results
        $reviews = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        // Statistics
        $stats = [
            'total_reviews' => Review::count(),
            'flagged_reviews' => Review::where('flagged', true)->count(),
            'average_rating' => round(Review::avg('rating'), 1),
            'reviews_today' => Review::whereDate('created_at', now()->toDateString())->count(),
        ];
        
        return view('admin.reviews', compact(
            'user',
            'reviews',
            'stats',
            'searchQuery',
            'ratingFilter',
            'flaggedFilter',
            'dateFrom',
            'dateTo',
            'perPage'
        ));
    }
    
    /**
     * Admin - Delete Review
     */
    public function deleteReview($id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }
        
        try {
            $review = Review::findOrFail($id);
            
            // Delete associated files if they exist
            if ($review->image_path && \Storage::exists($review->image_path)) {
                \Storage::delete($review->image_path);
            }
            if ($review->video_path && \Storage::exists($review->video_path)) {
                \Storage::delete($review->video_path);
            }
            
            $review->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Admin - Flag/Unflag Review
     */
    public function flagReview($id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }
        
        try {
            $review = Review::with(['consumer', 'establishment'])->findOrFail($id);
            $wasFlagged = $review->flagged;
            $review->flagged = !$review->flagged;
            $review->flagged_at = $review->flagged ? now() : null;
            $review->save();
            
            // Log the action in System Logs
            $action = $review->flagged ? 'flagged' : 'unflagged';
            $consumerName = $review->consumer 
                ? ($review->consumer->fname . ' ' . $review->consumer->lname) 
                : 'Unknown';
            $establishmentName = $review->establishment 
                ? ($review->establishment->business_name ?? 'Unknown') 
                : 'Unknown';
            
            SystemLog::log(
                'review_management',
                'review_' . $action,
                "Review {$action} by admin: Review ID {$review->id} from {$consumerName} for {$establishmentName}",
                $review->flagged ? 'warning' : 'info',
                'success',
                [
                    'review_id' => $review->id,
                    'consumer_id' => $review->consumer_id,
                    'consumer_name' => $consumerName,
                    'establishment_id' => $review->establishment_id,
                    'establishment_name' => $establishmentName,
                    'food_listing_id' => $review->food_listing_id,
                    'rating' => $review->rating,
                    'action' => $action,
                    'flagged' => $review->flagged,
                    'flagged_at' => $review->flagged_at?->toDateTimeString(),
                    'admin_id' => session('user_id'),
                    'admin_email' => session('user_email'),
                ]
            );
            
            // Notify admin when review is flagged (not when unflagged)
            if ($review->flagged) {
                try {
                    \App\Services\AdminNotificationService::notifyReviewFlagged($review);
                } catch (\Exception $e) {
                    \Log::error('Failed to create admin notification for flagged review: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => $review->flagged ? 'Review flagged successfully.' : 'Review unflagged successfully.',
                'flagged' => $review->flagged
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review flag status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin - Get Review Details
     */
    public function getReviewDetails($id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }
        
        try {
            \Log::info('Fetching review details', ['review_id' => $id]);
            $review = Review::with([
                'consumer' => function($query) {
                    $query->select('consumer_id', 'fname', 'lname', 'email', 'phone_no', 'address', 'created_at');
                },
                'establishment' => function($query) {
                    $query->select('establishment_id', 'business_name', 'email', 'phone_no', 'address', 'created_at');
                },
                'foodListing' => function($query) {
                    $query->select('id', 'name', 'description', 'original_price', 'discount_percentage', 'image_path');
                },
                'order' => function($query) {
                    $query->select('id', 'order_number', 'status', 'total_price', 'delivery_method', 'created_at');
                }
            ])->find($id);
            
            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found.'
                ], 404);
            }
            
            // Format image and video paths
            $imagePath = null;
            if ($review->image_path) {
                try {
                    // Check if it's already a full URL
                    if (filter_var($review->image_path, FILTER_VALIDATE_URL)) {
                        $imagePath = $review->image_path;
                    } else {
                        $imagePath = Storage::url($review->image_path);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to generate image URL for review ' . $review->id . ': ' . $e->getMessage());
                    $imagePath = $review->image_path; // Fallback to original path
                }
            }
            
            $videoPath = null;
            if ($review->video_path) {
                try {
                    // Check if it's already a full URL
                    if (filter_var($review->video_path, FILTER_VALIDATE_URL)) {
                        $videoPath = $review->video_path;
                    } else {
                        $videoPath = Storage::url($review->video_path);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to generate video URL for review ' . $review->id . ': ' . $e->getMessage());
                    $videoPath = $review->video_path; // Fallback to original path
                }
            }
            
            // Format the review data
            $reviewData = [
                'id' => $review->id,
                'rating' => $review->rating,
                'description' => $review->description,
                'image_path' => $imagePath,
                'video_path' => $videoPath,
                'flagged' => $review->flagged ?? false,
                'flagged_at' => $review->flagged_at ? $review->flagged_at->format('M d, Y h:i A') : null,
                'created_at' => $review->created_at ? $review->created_at->format('M d, Y h:i A') : 'N/A',
                'updated_at' => $review->updated_at ? $review->updated_at->format('M d, Y h:i A') : 'N/A',
                'consumer' => $review->consumer ? [
                    'name' => trim(($review->consumer->fname ?? '') . ' ' . ($review->consumer->lname ?? '')) ?: 'N/A',
                    'email' => $review->consumer->email ?? 'N/A',
                    'phone_no' => $review->consumer->phone_no ?? null,
                    'address' => $review->consumer->address ?? null,
                    'member_since' => $review->consumer->created_at ? $review->consumer->created_at->format('M d, Y') : 'N/A',
                ] : null,
                'establishment' => $review->establishment ? [
                    'name' => $review->establishment->business_name ?? 'N/A',
                    'email' => $review->establishment->email ?? 'N/A',
                    'phone_no' => $review->establishment->phone_no ?? null,
                    'address' => $review->establishment->address ?? null,
                    'member_since' => $review->establishment->created_at ? $review->establishment->created_at->format('M d, Y') : 'N/A',
                ] : null,
                'food_listing' => $review->foodListing ? [
                    'id' => $review->foodListing->id,
                    'name' => $review->foodListing->name,
                    'description' => $review->foodListing->description,
                    'original_price' => $review->foodListing->original_price,
                    'discount_percentage' => $review->foodListing->discount_percentage,
                    'image_url' => $review->foodListing->image_path ? Storage::url($review->foodListing->image_path) : null,
                ] : null,
                'order' => $review->order ? [
                    'id' => $review->order->id,
                    'order_number' => $review->order->order_number ?? 'N/A',
                    'status' => $review->order->status ?? 'N/A',
                    'total_price' => $review->order->total_price ?? 0,
                    'delivery_method' => $review->order->delivery_method ?? null,
                    'created_at' => $review->order->created_at ? $review->order->created_at->format('M d, Y h:i A') : 'N/A',
                ] : null,
            ];
            
            \Log::info('Review details formatted successfully', ['review_id' => $id]);
            
            return response()->json([
                'success' => true,
                'review' => $reviewData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching review details', [
                'review_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch review details.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while fetching review details.'
            ], 500);
        }
    }

    /**
     * Admin - System Settings
     */
    public function adminSettings()
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        return view('admin.settings', compact('user'));
    }

    /**
     * Calculate discounted price based on original price and discount percentage
     */
    private function calculateDiscountedPrice($originalPrice, $discountPercentage)
    {
        $originalPrice = (float) $originalPrice;
        $discountPercentage = (float) $discountPercentage;
        
        if ($discountPercentage <= 0) {
            return $originalPrice;
        }
        
        $discountAmount = $originalPrice * ($discountPercentage / 100);
        $discountedPrice = $originalPrice - $discountAmount;
        
        return round($discountedPrice, 2);
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        $user = $this->getUserData();
        $userType = session('user_type', 'consumer');
        
        return view($userType . '.profile', compact('user', 'userType'));
    }

    /**
     * Admin - Foodbanks Management
     */
    public function adminFoodbanks(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        
        // Get filter parameters
        $searchQuery = $request->get('search', '');
        $statusFilter = $request->get('status', 'all');
        $verifiedFilter = $request->get('verified', 'all');
        
        // Query with eager loading
        $foodbanksQuery = Foodbank::withCount([
            'donationRequests as total_requests_count',
            'donations as total_donations_count'
        ]);
        
        // Apply search filter
        if ($searchQuery) {
            $foodbanksQuery->where(function($query) use ($searchQuery) {
                $query->where('organization_name', 'like', "%{$searchQuery}%")
                      ->orWhere('email', 'like', "%{$searchQuery}%")
                      ->orWhere('username', 'like', "%{$searchQuery}%")
                      ->orWhere('contact_person', 'like', "%{$searchQuery}%");
            });
        }
        
        // Apply status filter
        if ($statusFilter !== 'all') {
            $foodbanksQuery->where('status', $statusFilter);
        }
        
        // Apply verified filter
        if ($verifiedFilter !== 'all') {
            $foodbanksQuery->where('verification_status', $verifiedFilter);
        }
        
        // Get foodbanks
        $foodbanks = $foodbanksQuery->orderBy('created_at', 'desc')->get();
        
        // Format foodbanks data
        $formattedFoodbanks = $foodbanks->map(function($foodbank) {
            return [
                'id' => $foodbank->foodbank_id,
                'organization_name' => $foodbank->organization_name,
                'contact_person' => $foodbank->contact_person,
                'email' => $foodbank->email,
                'phone_no' => $foodbank->phone_no,
                'address' => $foodbank->address,
                'registration_number' => $foodbank->registration_number,
                'username' => $foodbank->username,
                'status' => $foodbank->status ?? 'active',
                'verified' => ($foodbank->verification_status ?? 'unverified') === 'verified',
                'total_requests' => $foodbank->total_requests_count ?? 0,
                'total_donations' => $foodbank->total_donations_count ?? 0,
                'registered_at' => $foodbank->created_at,
                'profile_image' => $foodbank->profile_image,
            ];
        });
        
        // Statistics
        $stats = [
            'total' => Foodbank::count(),
            'verified' => Foodbank::where('verification_status', 'verified')->count(),
            'unverified' => Foodbank::where('verification_status', 'unverified')->count(),
            'active' => Foodbank::where('status', 'active')->count(),
            'suspended' => Foodbank::where('status', 'suspended')->count(),
        ];
        
        return view('admin.foodbanks', compact(
            'user',
            'formattedFoodbanks',
            'stats',
            'searchQuery',
            'statusFilter',
            'verifiedFilter'
        ));
    }

    /**
     * Admin - View Foodbank Details
     */
    public function viewFoodbankDetails($id)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        $foodbank = Foodbank::withCount([
            'donationRequests as total_requests_count',
            'donationRequests as pending_requests_count' => function($query) {
                $query->where('status', 'pending');
            },
            'donationRequests as accepted_requests_count' => function($query) {
                $query->where('status', 'accepted');
            },
            'donationRequests as completed_requests_count' => function($query) {
                $query->where('status', 'completed');
            },
            'donations as total_donations_count'
        ])->find($id);
        
        if (!$foodbank) {
            return redirect()->route('admin.foodbanks')->with('error', 'Foodbank not found.');
        }
        
        // Get donation requests
        $donationRequests = $foodbank->donationRequests()
            ->with(['establishment', 'donation'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        // Get donations
        $donations = $foodbank->donations()
            ->with(['establishment'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        // Get system logs related to this foodbank
        $systemLogs = \App\Models\SystemLog::where('metadata->foodbank_id', $foodbank->foodbank_id)
            ->orWhere(function($query) use ($foodbank) {
                $query->where('user_type', 'foodbank')
                      ->where('user_id', $foodbank->foodbank_id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
        
        return view('admin.foodbank-details', compact(
            'user',
            'foodbank',
            'donationRequests',
            'donations',
            'systemLogs'
        ));
    }

    /**
     * Admin - Update Foodbank Status
     */
    public function updateFoodbankStatus(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        $request->validate([
            'status' => 'required|in:active,suspended,deleted'
        ]);
        
        try {
            $foodbank = Foodbank::find($id);
            
            if (!$foodbank) {
                return response()->json(['success' => false, 'message' => 'Foodbank not found.'], 404);
            }
            
            $oldStatus = $foodbank->status;
            $foodbank->status = $request->status;
            $foodbank->save();
            
            $action = match($request->status) {
                'active' => 'activated',
                'suspended' => 'suspended',
                'deleted' => 'deleted',
                default => 'updated'
            };
            
            // Log action
            \App\Models\SystemLog::log(
                'admin_action',
                'foodbank_status_updated',
                "Admin {$action} foodbank: {$foodbank->organization_name}",
                'info',
                'success',
                [
                    'foodbank_id' => $foodbank->foodbank_id,
                    'foodbank_name' => $foodbank->organization_name,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'admin_id' => session('user_id'),
                    'admin_email' => session('user_email'),
                ]
            );
            
            // Send notification
            if ($request->status === 'suspended') {
                \App\Models\Notification::createNotification(
                    'foodbank',
                    $foodbank->foodbank_id,
                    'account_suspended',
                    'Account Suspended',
                    "Your foodbank account has been suspended by an administrator.",
                    ['foodbank_id' => $foodbank->foodbank_id]
                );
            } elseif ($request->status === 'active' && $oldStatus === 'suspended') {
                \App\Models\Notification::createNotification(
                    'foodbank',
                    $foodbank->foodbank_id,
                    'account_unsuspended',
                    'Account Unsuspended',
                    "Your foodbank account has been reactivated.",
                    ['foodbank_id' => $foodbank->foodbank_id]
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => "Foodbank {$action} successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update foodbank status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin - Toggle Foodbank Verification
     */
    public function toggleFoodbankVerification(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        $request->validate([
            'verification_status' => 'required|in:verified,unverified'
        ]);
        
        try {
            $foodbank = Foodbank::find($id);
            
            if (!$foodbank) {
                return response()->json(['success' => false, 'message' => 'Foodbank not found.'], 404);
            }
            
            $oldStatus = $foodbank->verification_status ?? 'unverified';
            $newStatus = $request->verification_status;
            
            // Update verification status
            $foodbank->verification_status = $newStatus;
            // Also update legacy verified field for backward compatibility
            $foodbank->verified = ($newStatus === 'verified');
            $foodbank->save();
            
            // Hide/restore donation requests based on verification status
            if ($oldStatus === 'verified' && $newStatus === 'unverified') {
                // Hide all active donation requests (set status to expired for foodbank's own requests)
                DonationRequest::where('foodbank_id', $foodbank->foodbank_id)
                    ->whereNull('establishment_id') // Only foodbank's own requests
                    ->whereIn('status', ['pending', 'active'])
                    ->update(['status' => 'expired']);
            } elseif ($oldStatus === 'unverified' && $newStatus === 'verified') {
                // Note: We can't automatically restore expired requests as they may have actually expired
                // This is a design decision - once hidden, they stay hidden
                // New requests will work once verified
            }
            
            $action = $newStatus === 'verified' ? 'verified' : 'unverified';
            
            // Log action
            \App\Models\SystemLog::log(
                'admin_action',
                'foodbank_verification_updated',
                "Admin {$action} foodbank: {$foodbank->organization_name}",
                'info',
                'success',
                [
                    'foodbank_id' => $foodbank->foodbank_id,
                    'foodbank_name' => $foodbank->organization_name,
                    'verification_status' => $newStatus,
                    'old_status' => $oldStatus,
                    'admin_id' => session('user_id'),
                    'admin_email' => session('user_email'),
                ]
            );
            
            // Send notification
            if ($newStatus === 'verified') {
                \App\Models\Notification::createNotification(
                    'foodbank',
                    $foodbank->foodbank_id,
                    'account_verified',
                    'Account Verified',
                    "Your foodbank account has been verified by an administrator.",
                    ['foodbank_id' => $foodbank->foodbank_id]
                );
            } else {
                \App\Models\Notification::createNotification(
                    'foodbank',
                    $foodbank->foodbank_id,
                    'account_unverified',
                    'Account Verification Removed',
                    "Your foodbank account verification has been removed by an administrator.",
                    ['foodbank_id' => $foodbank->foodbank_id]
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => "Foodbank {$action} successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update verification status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin - Delete Foodbank
     */
    public function deleteFoodbank($id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        try {
            $foodbank = Foodbank::find($id);
            
            if (!$foodbank) {
                return response()->json(['success' => false, 'message' => 'Foodbank not found.'], 404);
            }
            
            $foodbankName = $foodbank->organization_name;
            
            // Cascade delete: anonymize related data
            // Anonymize donations
            \App\Models\Donation::where('foodbank_id', $id)
                ->update([
                    'foodbank_id' => null,
                    'foodbank_notes' => 'Donation to deleted account',
                ]);
            
            // Anonymize donation requests
            \App\Models\DonationRequest::where('foodbank_id', $id)
                ->update([
                    'foodbank_id' => null,
                    'contact_name' => 'Deleted Account',
                    'email' => 'deleted@account.local',
                    'phone_number' => null,
                ]);
            
            // Delete notifications
            \App\Models\Notification::where('user_type', 'foodbank')
                ->where('user_id', $id)
                ->delete();
            
            // Log action before deletion
            \App\Models\SystemLog::log(
                'admin_action',
                'foodbank_deleted',
                "Admin deleted foodbank: {$foodbankName}",
                'warning',
                'success',
                [
                    'foodbank_id' => $foodbank->foodbank_id,
                    'foodbank_name' => $foodbankName,
                    'admin_id' => session('user_id'),
                    'admin_email' => session('user_email'),
                ]
            );
            
            // Delete the foodbank
            $foodbank->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Foodbank deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete foodbank.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin - Foodbank Donation Hub
     */
    public function adminFoodbankDonationHub(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        
        // Get filter parameters
        $foodbankFilter = $request->get('foodbank', 'all');
        $statusFilter = $request->get('status', 'all');
        $typeFilter = $request->get('type', 'all'); // requests or donations
        
        // Query donation requests
        $donationRequestsQuery = \App\Models\DonationRequest::with(['foodbank', 'establishment', 'donation'])
            ->whereNotNull('foodbank_id');
        
        // Query donations
        $donationsQuery = \App\Models\Donation::with(['foodbank', 'establishment'])
            ->whereNotNull('foodbank_id');
        
        // Apply foodbank filter
        if ($foodbankFilter !== 'all') {
            $donationRequestsQuery->where('foodbank_id', $foodbankFilter);
            $donationsQuery->where('foodbank_id', $foodbankFilter);
        }
        
        // Apply status filter
        if ($statusFilter !== 'all') {
            $donationRequestsQuery->where('status', $statusFilter);
            $donationsQuery->where('status', $statusFilter);
        }
        
        $donationRequests = $donationRequestsQuery->orderBy('created_at', 'desc')->limit(100)->get();
        $donations = $donationsQuery->orderBy('created_at', 'desc')->limit(100)->get();
        
        // Get all foodbanks for filter dropdown
        $foodbanks = Foodbank::orderBy('organization_name')->get();
        
        // Statistics
        $stats = [
            'total_requests' => \App\Models\DonationRequest::whereNotNull('foodbank_id')->count(),
            'pending_requests' => \App\Models\DonationRequest::whereNotNull('foodbank_id')->where('status', 'pending')->count(),
            'accepted_requests' => \App\Models\DonationRequest::whereNotNull('foodbank_id')->where('status', 'accepted')->count(),
            'completed_requests' => \App\Models\DonationRequest::whereNotNull('foodbank_id')->where('status', 'completed')->count(),
            'total_donations' => \App\Models\Donation::whereNotNull('foodbank_id')->count(),
        ];
        
        return view('admin.foodbank-donation-hub', compact(
            'user',
            'donationRequests',
            'donations',
            'foodbanks',
            'stats',
            'foodbankFilter',
            'statusFilter',
            'typeFilter'
        ));
    }
    
    /**
     * Admin - View Deletion Requests
     */
    public function adminDeletionRequests(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        
        // Get filter parameters
        $statusFilter = $request->get('status', 'all');
        $roleFilter = $request->get('role', 'all');
        $searchQuery = $request->get('search', '');
        
        // Build query
        $query = DeletionRequest::query();
        
        // Apply status filter
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }
        
        // Apply role filter
        if ($roleFilter !== 'all') {
            $query->where('user_type', $roleFilter);
        }
        
        // Apply search filter
        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('reason', 'like', "%{$searchQuery}%")
                  ->orWhere('admin_notes', 'like', "%{$searchQuery}%");
            });
        }
        
        // Get requests with user information
        $requests = $query->orderBy('created_at', 'desc')->get()->map(function($request) {
            $userModel = $request->getUser();
            $userName = 'Unknown';
            $userEmail = 'N/A';
            
            if ($userModel) {
                if ($request->user_type === 'consumer') {
                    $userName = ($userModel->fname ?? '') . ' ' . ($userModel->lname ?? '');
                    $userEmail = $userModel->email ?? 'N/A';
                } elseif ($request->user_type === 'establishment') {
                    $userName = $userModel->business_name ?? (($userModel->owner_fname ?? '') . ' ' . ($userModel->owner_lname ?? ''));
                    $userEmail = $userModel->email ?? 'N/A';
                } elseif ($request->user_type === 'foodbank') {
                    $userName = $userModel->organization_name ?? 'Foodbank';
                    $userEmail = $userModel->email ?? 'N/A';
                }
            }
            
            return [
                'id' => $request->id,
                'user_id' => $request->user_id,
                'user_type' => $request->user_type,
                'user_name' => $userName,
                'user_email' => $userEmail,
                'reason' => $request->reason,
                'status' => $request->status,
                'admin_notes' => $request->admin_notes,
                'approved_by' => $request->approved_by,
                'approved_at' => $request->approved_at,
                'created_at' => $request->created_at,
                'updated_at' => $request->updated_at,
            ];
        });
        
        // Statistics
        $stats = [
            'total' => DeletionRequest::count(),
            'pending' => DeletionRequest::where('status', 'pending')->count(),
            'approved' => DeletionRequest::where('status', 'approved')->count(),
            'rejected' => DeletionRequest::where('status', 'rejected')->count(),
        ];
        
        return view('admin.deletion-requests', compact(
            'user',
            'requests',
            'stats',
            'statusFilter',
            'roleFilter',
            'searchQuery'
        ));
    }
    
    /**
     * Admin - Approve Deletion Request
     */
    public function approveDeletionRequest(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $deletionRequest = DeletionRequest::find($id);
            
            if (!$deletionRequest) {
                return response()->json(['success' => false, 'message' => 'Deletion request not found.'], 404);
            }
            
            if ($deletionRequest->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'This request has already been processed.'], 400);
            }
            
            $userId = $deletionRequest->user_id;
            $userType = $deletionRequest->user_type;
            
            // Get user model
            $model = match($userType) {
                'consumer' => Consumer::find($userId),
                'establishment' => Establishment::find($userId),
                'foodbank' => Foodbank::find($userId),
                default => null
            };
            
            if (!$model) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }
            
            $userName = $this->getUserName($model, $userType);
            
            // Step 1: Delete personal content
            $this->deletePersonalContent($model, $userType);
            
            // Step 2: Anonymize critical business records
            $this->anonymizeCriticalRecords($model, $userType, $userId, $userName);
            
            // Step 3: Delete the user record
            $model->delete();
            
            // Step 4: Update deletion request
            $deletionRequest->status = 'approved';
            // approved_by is UUID type, but admin users have integer IDs
            // Store admin ID in admin_notes metadata instead, or leave null
            $deletionRequest->approved_by = null; // Admin users use integer IDs, not UUIDs
            $deletionRequest->approved_at = now();
            $adminNotes = $request->input('admin_notes', '');
            $adminId = session('user_id');
            $deletionRequest->admin_notes = $adminNotes . ($adminNotes ? "\n\n" : '') . "Approved by Admin ID: {$adminId}";
            $deletionRequest->save();
            
            // Step 5: Log the action in System Logs
            SystemLog::log(
                'account_deletion',
                'account_deleted',
                "Account deletion approved and user account permanently deleted: {$userName} ({$userType})",
                'critical',
                'success',
                [
                    'deletion_request_id' => $deletionRequest->id,
                    'deleted_user_id' => $userId,
                    'deleted_user_type' => $userType,
                    'deleted_user_name' => $userName,
                    'approved_by_admin_id' => session('user_id'),
                    'admin_notes' => $request->input('admin_notes'),
                ]
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Account deletion approved and account removed successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Deletion request approval failed: ' . $e->getMessage(), [
                'request_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve deletion request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Admin - Decline Deletion Request
     */
    public function declineDeletionRequest(Request $request, $id)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        
        try {
            $deletionRequest = DeletionRequest::find($id);
            
            if (!$deletionRequest) {
                return response()->json(['success' => false, 'message' => 'Deletion request not found.'], 404);
            }
            
            if ($deletionRequest->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'This request has already been processed.'], 400);
            }
            
            // Get user information for logging
            $userModel = $deletionRequest->getUser();
            $userName = 'Unknown';
            $userEmail = 'N/A';
            
            if ($userModel) {
                if ($deletionRequest->user_type === 'consumer') {
                    $userName = ($userModel->fname ?? '') . ' ' . ($userModel->lname ?? '');
                    $userEmail = $userModel->email ?? 'N/A';
                } elseif ($deletionRequest->user_type === 'establishment') {
                    $userName = $userModel->business_name ?? (($userModel->owner_fname ?? '') . ' ' . ($userModel->owner_lname ?? ''));
                    $userEmail = $userModel->email ?? 'N/A';
                } elseif ($deletionRequest->user_type === 'foodbank') {
                    $userName = $userModel->organization_name ?? 'Foodbank';
                    $userEmail = $userModel->email ?? 'N/A';
                }
            }
            
            $deletionRequest->status = 'rejected';
            // approved_by is UUID type, but admin users have integer IDs
            // Store admin ID in admin_notes metadata instead, or leave null
            $deletionRequest->approved_by = null; // Admin users use integer IDs, not UUIDs
            $deletionRequest->approved_at = now();
            $adminNotes = $request->input('admin_notes', '');
            $adminId = session('user_id');
            $deletionRequest->admin_notes = $adminNotes . ($adminNotes ? "\n\n" : '') . "Declined by Admin ID: {$adminId}";
            $deletionRequest->save();
            
            // Log the action in System Logs
            SystemLog::log(
                'account_deletion',
                'deletion_request_declined',
                "Deletion request declined for user: {$userName} ({$deletionRequest->user_type})",
                'info',
                'success',
                [
                    'deletion_request_id' => $deletionRequest->id,
                    'user_id' => $deletionRequest->user_id,
                    'user_type' => $deletionRequest->user_type,
                    'user_name' => $userName,
                    'user_email' => $userEmail,
                    'declined_by_admin_id' => session('user_id'),
                    'admin_notes' => $request->input('admin_notes'),
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Deletion request declined successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Deletion request decline failed: ' . $e->getMessage(), [
                'request_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to decline deletion request: ' . $e->getMessage()
            ], 500);
        }
    }
}
