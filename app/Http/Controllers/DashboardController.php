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
use App\Models\HelpCenterArticle;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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
        
        // Initialize statistics with default values
        $totalSavings = 0;
        $ordersCount = 0;
        $foodRescued = 0;
        $ratedOrdersCount = 0;
        
        if ($consumerId) {
            // Get all completed orders for this consumer
            $completedOrders = Order::with('foodListing')
                ->where('consumer_id', $consumerId)
                ->where('status', 'completed')
                ->get();
            
            // Calculate Total Savings (money saved from discounts)
            foreach ($completedOrders as $order) {
                if ($order->foodListing) {
                    $foodListing = $order->foodListing;
                    $originalPrice = (float) $foodListing->original_price;
                    $discountPercentage = (float) ($foodListing->discount_percentage ?? 0);
                    $quantity = (int) $order->quantity;
                    
                    if ($discountPercentage > 0) {
                        $discountAmountPerUnit = $originalPrice * ($discountPercentage / 100);
                        $totalSavings += $discountAmountPerUnit * $quantity;
                    }
                    
                    // Calculate food rescued (total quantity)
                    $foodRescued += $quantity;
                }
            }
            
            $totalSavings = round($totalSavings, 2);
            $ordersCount = $completedOrders->count();
            
            // Calculate number of rated orders (orders that have reviews)
            $ratedOrdersCount = Review::where('consumer_id', $consumerId)
                ->distinct('order_id')
                ->count('order_id');
        }
        
        // Get upcoming order (pending or accepted status)
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
        
        // Get random food listings with discounts (best deals)
        // Prioritize items with higher discount percentages
        $bestDeals = FoodListing::where('status', 'active')
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
                $discountedPrice = $this->calculateDiscountedPrice($item->original_price, $item->discount_percentage);
                
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
        
        // Weekly data for chart (last 7 days)
        $dayLabels = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayOfWeek = $date->dayOfWeek; // 0=Sunday, 1=Monday, etc.
            
            // Sample data - replace with actual database queries when ready
            $dayFoodReceived = 0; // TODO: Query actual food received for this day
            
            $weeklyData[] = [
                'label' => $dayLabels[$dayOfWeek],
                'value' => (int) $dayFoodReceived
            ];
        }
        
        // If no data, use sample data
        if (array_sum(array_column($weeklyData, 'value')) === 0) {
            $weeklyData = [
                ['label' => 'SUN', 'value' => 5],
                ['label' => 'MON', 'value' => 8],
                ['label' => 'TUE', 'value' => 12],
                ['label' => 'WED', 'value' => 15],
                ['label' => 'THU', 'value' => 10],
                ['label' => 'FRI', 'value' => 18],
                ['label' => 'SAT', 'value' => 14],
            ];
        }
        
        return view('foodbank.dashboard', compact('user', 'weeklyData'));
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
        // Get published help articles
        $articles = HelpCenterArticle::published()
            ->orderBy('display_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get unique categories
        $categories = HelpCenterArticle::published()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        return view('foodbank.help', compact('articles', 'categories'));
    }

    /**
     * Show settings page for foodbank
     */
    public function foodbankSettings()
    {
        $userData = $this->getUserData();
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
        
        // Fetch donation requests published by this foodbank
        $donationRequests = DonationRequest::where('foodbank_id', $foodbankId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->donation_request_id,
                    'foodType' => $request->item_name,
                    'quantity' => $request->quantity,
                    'matches' => $request->matches,
                    'status' => $request->status,
                ];
            })
            ->toArray();
        
        // Fetch donation offers from establishments (pending donations)
        $establishmentDonations = Donation::where('foodbank_id', $foodbankId)
            ->whereIn('status', ['pending_pickup', 'ready_for_collection'])
            ->with(['establishment'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($donation) {
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
                    'donation_number' => $donation->donation_number,
                    'establishment_id' => $donation->establishment_id,
                    'establishment_name' => $establishment->business_name ?? 'Unknown',
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
                    'establishment_notes' => $donation->establishment_notes,
                    'is_urgent' => $donation->is_urgent,
                    'is_nearing_expiry' => $donation->is_nearing_expiry,
                ];
            })
            ->toArray();
        
        return view('foodbank.donation-request', compact('user', 'donationRequests', 'establishmentDonations'));
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

        $foodbankId = session('user_id');

        // Validate the request
        $validated = $request->validate([
            'itemName' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'distributionZone' => 'required|string',
            'dropoffDate' => 'required|date|after_or_equal:today',
            'timeOption' => 'required|in:allDay,anytime,specific',
            'startTime' => 'nullable|required_if:timeOption,specific|date_format:H:i',
            'endTime' => 'nullable|required_if:timeOption,specific|date_format:H:i|after:startTime',
            'address' => 'required|string',
            'deliveryOption' => 'required|in:pickup,delivery',
            'contactName' => 'required|string|max:255',
            'phoneNumber' => 'required|string',
            'email' => 'required|email|max:255',
        ]);

        try {
            // Create the donation request
            $donationRequest = DonationRequest::create([
                'foodbank_id' => $foodbankId,
                'item_name' => $validated['itemName'],
                'quantity' => $validated['quantity'],
                'category' => $validated['category'],
                'description' => $validated['description'] ?? null,
                'distribution_zone' => $validated['distributionZone'],
                'dropoff_date' => $validated['dropoffDate'],
                'time_option' => $validated['timeOption'],
                'start_time' => $validated['timeOption'] === 'specific' ? $validated['startTime'] : null,
                'end_time' => $validated['timeOption'] === 'specific' ? $validated['endTime'] : null,
                'address' => $validated['address'],
                'delivery_option' => $validated['deliveryOption'],
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
                ->first();

            if (!$donation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation not found.'
                ], 404);
            }

            // Update status to ready_for_collection
            $donation->status = 'ready_for_collection';
            $donation->save();
            
            // Reload donation with relationships for notification
            $donation->load(['foodbank', 'establishment']);
            
            // Send notification to establishment
            NotificationService::notifyDonationApproved($donation);

            return response()->json([
                'success' => true,
                'message' => 'Donation accepted successfully!',
                'data' => [
                    'id' => $donation->donation_id,
                    'status' => $donation->status,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept donation. Please try again.',
                'error' => $e->getMessage()
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
                ->first();

            if (!$donation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation not found.'
                ], 404);
            }

            // Update status to cancelled
            $donation->status = 'cancelled';
            $donation->save();

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
        
        // Fetch all establishments from database
        $establishments = Establishment::all();
        
        // Map establishments to partner format expected by frontend
        $partners = $establishments->map(function ($establishment) {
            // Calculate average rating from reviews (if available)
            $reviews = Review::where('establishment_id', $establishment->establishment_id)->get();
            $rating = $reviews->count() > 0 
                ? round($reviews->avg('rating'), 1) 
                : 4.5; // Default rating if no reviews
            
            // Count donations (completed orders or donation requests fulfilled)
            // For now, using order count as donation count (can be updated when donation system is ready)
            $donations = Order::where('establishment_id', $establishment->establishment_id)
                ->where('status', 'completed')
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
                // Additional fields for modal details
                'email' => $establishment->email,
                'phone' => $establishment->phone_no,
                'owner' => $establishment->owner_fname . ' ' . $establishment->owner_lname,
                'registered_at' => $establishment->registered_at ? $establishment->registered_at->format('F Y') : 'N/A',
            ];
        })->toArray();
        
        // Calculate stats
        $totalPartners = count($partners);
        
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
        
        // Build query with filters
        $query = Donation::where('foodbank_id', $foodbankId)
            ->with(['establishment', 'donationRequest']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('category')) {
            $query->where('item_category', $request->category);
        }
        
        if ($request->filled('establishment_id')) {
            $query->where('establishment_id', $request->establishment_id);
        }
        
        if ($request->filled('date_from')) {
            $query->where('scheduled_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('scheduled_date', '<=', $request->date_to);
        }
        
        // Get all donations for filtering options
        $allDonations = Donation::where('foodbank_id', $foodbankId)->get();
        
        // Get unique categories and establishments for filters
        $categories = $allDonations->pluck('item_category')->unique()->sort()->values();
        $establishments = Establishment::whereIn('establishment_id', $allDonations->pluck('establishment_id')->unique())
            ->get(['establishment_id', 'business_name'])
            ->map(function ($est) {
                return ['id' => $est->establishment_id, 'name' => $est->business_name];
            });
        
        // Get filtered donations
        $donations = $query->orderBy('created_at', 'desc')->get()->map(function ($donation) {
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
            ];
        })->toArray();
        
        // Calculate statistics
        $stats = [
            'total_donations' => $allDonations->count(),
            'total_quantity' => $allDonations->sum('quantity'),
            'establishment_participation' => $allDonations->pluck('establishment_id')->unique()->count(),
            'by_status' => [
                'pending_pickup' => $allDonations->where('status', 'pending_pickup')->count(),
                'ready_for_collection' => $allDonations->where('status', 'ready_for_collection')->count(),
                'collected' => $allDonations->where('status', 'collected')->count(),
                'cancelled' => $allDonations->where('status', 'cancelled')->count(),
                'expired' => $allDonations->where('status', 'expired')->count(),
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
                
                $query = Donation::where('foodbank_id', $foodbankId)
                    ->with(['establishment']);
                
                // Apply filters if provided
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                
                if ($request->filled('category')) {
                    $query->where('item_category', $request->category);
                }
                
                if ($request->filled('establishment_id')) {
                    $query->where('establishment_id', $request->establishment_id);
                }
                
                if ($request->filled('date_from')) {
                    $query->where('scheduled_date', '>=', $request->date_from);
                }
                
                if ($request->filled('date_to')) {
                    $query->where('scheduled_date', '<=', $request->date_to);
                }
                
                $donations = $query->orderBy('created_at', 'desc')->get();
                
                foreach ($donations as $donation) {
                    $data[] = [
                        $donation->donation_id,
                        $donation->donation_number,
                        $donation->establishment->business_name ?? 'Unknown',
                        $donation->item_name,
                        $donation->item_category,
                        $donation->quantity,
                        $donation->unit,
                        ucfirst(str_replace('_', ' ', $donation->status)),
                        $donation->scheduled_date->format('Y-m-d'),
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
     * Show impact reports page for foodbank
     */
    public function foodbankImpactReports()
    {
        // Verify user is a foodbank
        if (session('user_type') !== 'foodbank') {
            return redirect()->route('login')->with('error', 'Access denied. Please login as a foodbank.');
        }
        
        $user = $this->getUserData();
        
        // Sample data - replace with actual database queries when ready
        $foodRequests = 10;
        $foodReceived = 320;
        $familiesServed = 87;
        $totalImpactScore = 95;
        
        // Chart data matching consumer's my-impact format
        $dailyData = [
            ['label' => 'SUN', 'value' => 5],
            ['label' => 'MON', 'value' => 8],
            ['label' => 'TUE', 'value' => 12],
            ['label' => 'WED', 'value' => 15],
            ['label' => 'THU', 'value' => 10],
            ['label' => 'FRI', 'value' => 18],
            ['label' => 'SAT', 'value' => 14],
        ];
        
        $monthlyData = [
            ['label' => 'JAN', 'value' => 100],
            ['label' => 'FEB', 'value' => 80],
            ['label' => 'MAR', 'value' => 120],
            ['label' => 'APR', 'value' => 150],
            ['label' => 'MAY', 'value' => 200],
            ['label' => 'JUN', 'value' => 180],
            ['label' => 'JUL', 'value' => 220],
            ['label' => 'AUG', 'value' => 250],
            ['label' => 'SEP', 'value' => 190],
            ['label' => 'OCT', 'value' => 210],
            ['label' => 'NOV', 'value' => 230],
            ['label' => 'DEC', 'value' => 280],
        ];
        
        $yearlyData = [
            ['label' => '2020', 'value' => 1200],
            ['label' => '2021', 'value' => 1800],
            ['label' => '2022', 'value' => 2400],
            ['label' => '2023', 'value' => 3000],
            ['label' => '2024', 'value' => 3500],
        ];
        
        // Top Establishment Contributors
        $foodbankId = session('user_id');
        $topContributors = Donation::where('foodbank_id', $foodbankId)
            ->whereIn('status', ['collected', 'ready_for_collection'])
            ->with('establishment')
            ->get()
            ->groupBy('establishment_id')
            ->map(function ($donations) {
                $establishment = $donations->first()->establishment;
                $totalQuantity = $donations->sum('quantity');
                return [
                    'establishment_id' => $donations->first()->establishment_id,
                    'establishment_name' => $establishment->business_name ?? 'Unknown',
                    'total_quantity' => $totalQuantity,
                ];
            })
            ->sortByDesc('total_quantity')
            ->take(5)
            ->values();
        
        // Calculate total food received for percentage calculation
        $totalFoodReceived = Donation::where('foodbank_id', $foodbankId)
            ->whereIn('status', ['collected', 'ready_for_collection'])
            ->sum('quantity');
        
        // Calculate percentages and assign colors
        $colors = ['#f5cd79', '#ff6b6b', '#7AB267', '#347928', '#9DCF86'];
        $topContributorsData = $topContributors->map(function ($contributor, $index) use ($totalFoodReceived, $colors) {
            $percentage = $totalFoodReceived > 0 ? ($contributor['total_quantity'] / $totalFoodReceived) * 100 : 0;
            return [
                'rank' => $index + 1,
                'establishment_name' => $contributor['establishment_name'],
                'total_quantity' => $contributor['total_quantity'],
                'percentage' => round($percentage, 2),
                'color' => $colors[$index % count($colors)],
            ];
        })->toArray();
        
        // Reports data
        $reports = [
            ['name' => 'Monthly Impact Report - October 2024', 'date' => 'Nov 5, 2024', 'period' => 'Monthly', 'format' => 'PDF'],
            ['name' => 'Quarterly Impact Summary Q3 2024', 'date' => 'Oct 15, 2024', 'period' => 'Quarterly', 'format' => 'PDF'],
            ['name' => 'Annual Impact Report 2023', 'date' => 'Jan 10, 2024', 'period' => 'Annual', 'format' => 'PDF'],
            ['name' => 'Donation Trends Analysis', 'date' => 'Oct 1, 2024', 'period' => 'Custom', 'format' => 'XLSX'],
            ['name' => 'Partner Network Impact', 'date' => 'Sep 20, 2024', 'period' => 'Monthly', 'format' => 'PDF'],
        ];
        
        return view('foodbank.impact-reports', compact(
            'user',
            'foodRequests',
            'foodReceived',
            'familiesServed',
            'totalImpactScore',
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
        // USER STATISTICS BY ROLE
        // ============================================
        $totalConsumers = Consumer::count();
        $totalEstablishments = Establishment::count();
        $totalFoodbanks = Foodbank::count();
        $totalUsers = $totalConsumers + $totalEstablishments + $totalFoodbanks;
        
        // ============================================
        // FOOD LISTINGS STATISTICS
        // ============================================
        $totalActiveListings = FoodListing::where('status', 'active')
            ->where('expiry_date', '>=', Carbon::now()->toDateString())
            ->count();
        $totalListings = FoodListing::count();
        
        // ============================================
        // ORDERS STATISTICS BY STATUS
        // ============================================
        $ordersByStatus = [
            'pending' => Order::where('status', 'pending')->count(),
            'accepted' => Order::where('status', 'accepted')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];
        $totalOrders = array_sum($ordersByStatus);
        
        // ============================================
        // DONATIONS STATISTICS
        // ============================================
        $totalDonations = Donation::count();
        $completedDonations = Donation::whereIn('status', ['collected', 'ready_for_collection'])->count();
        $pendingDonations = Donation::whereIn('status', ['pending_pickup', 'ready_for_collection'])->count();
        
        // ============================================
        // FOOD RESCUED STATISTICS
        // ============================================
        // From completed orders
        $foodRescuedFromOrders = Order::where('status', 'completed')->sum('quantity');
        
        // From completed donations
        $foodRescuedFromDonations = Donation::whereIn('status', ['collected', 'ready_for_collection'])
            ->sum('quantity');
        
        $totalFoodRescued = $foodRescuedFromOrders + $foodRescuedFromDonations;
        
        // Format food rescued
        $foodRescuedFormatted = $totalFoodRescued >= 1000 
            ? number_format($totalFoodRescued / 1000, 1) . 'K' 
            : number_format($totalFoodRescued);
        
        // ============================================
        // MONTHLY ACTIVITY DATA (Last 6 months)
        // ============================================
        $monthlyActivity = [];
        $months = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            
            $monthName = $date->format('M Y');
            $months[] = $monthName;
            
            // Count activities for this month
            $monthlyActivity[] = [
                'month' => $monthName,
                'users' => Consumer::whereBetween('created_at', [$monthStart, $monthEnd])->count() +
                          Establishment::whereBetween('created_at', [$monthStart, $monthEnd])->count() +
                          Foodbank::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'orders' => Order::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'donations' => Donation::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'listings' => FoodListing::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
            ];
        }
        
        // ============================================
        // RECENT ACTIVITY
        // ============================================
        $recentConsumers = Consumer::orderBy('created_at', 'desc')->limit(3)->get();
        $recentEstablishments = Establishment::orderBy('created_at', 'desc')->limit(3)->get();
        $recentDonations = Donation::whereIn('status', ['collected', 'ready_for_collection'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // ============================================
        // USER ENGAGEMENT
        // ============================================
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $activeConsumers = Consumer::where('created_at', '>=', $thirtyDaysAgo)->count();
        $activeEstablishments = Establishment::where('created_at', '>=', $thirtyDaysAgo)->count();
        $activeFoodbanks = Foodbank::where('created_at', '>=', $thirtyDaysAgo)->count();
        $totalActiveUsers = $activeConsumers + $activeEstablishments + $activeFoodbanks;
        $engagementPercentage = $totalUsers > 0 ? round(($totalActiveUsers / $totalUsers) * 100) : 0;
        
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
     * Delete personal content (reviews, food listings)
     */
    private function deletePersonalContent($model, $role)
    {
        if ($role === 'consumer') {
            // Delete reviews written by consumer
            Review::where('consumer_id', $model->consumer_id)->delete();
        } elseif ($role === 'establishment') {
            // Delete food listings created by establishment
            FoodListing::where('establishment_id', $model->establishment_id)->delete();
            
            // Delete reviews about this establishment (personal opinions)
            Review::where('establishment_id', $model->establishment_id)->delete();
        } elseif ($role === 'foodbank') {
            // Foodbanks don't have personal content to delete
        }
    }
    
    /**
     * Anonymize critical business records (orders, donations, transactions)
     */
    private function anonymizeCriticalRecords($model, $role, $userId, $userName)
    {
        if ($role === 'consumer') {
            // Anonymize orders
            Order::where('consumer_id', $userId)
                ->update([
                    'consumer_id' => null,
                    'customer_name' => 'Deleted User',
                    'customer_phone' => null,
                ]);
        } elseif ($role === 'establishment') {
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
        $violationsFilter = $request->get('violations', 'all');
        
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
            $establishmentsQuery->where('verified', $verifiedFilter === 'verified');
        }
        
        // Apply violations filter
        if ($violationsFilter !== 'all') {
            if ($violationsFilter === 'has_violations') {
                $establishmentsQuery->where('violations_count', '>', 0);
            } else {
                $establishmentsQuery->where('violations_count', 0);
            }
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
            $violations = $establishment->violations ?? [];
            
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
                'verified' => $establishment->verified ?? false,
                'violations_count' => $establishment->violations_count ?? 0,
                'violations' => $violations,
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
            'verified' => Establishment::where('verified', true)->count(),
            'unverified' => Establishment::where('verified', false)->count(),
            'active' => Establishment::where('status', 'active')->count(),
            'suspended' => Establishment::where('status', 'suspended')->count(),
            'with_violations' => Establishment::where('violations_count', '>', 0)->count(),
        ];
        
        return view('admin.establishments', compact(
            'user',
            'formattedEstablishments',
            'stats',
            'searchQuery',
            'statusFilter',
            'verifiedFilter',
            'violationsFilter'
        ));
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
            
            $establishment->status = $request->status;
            $establishment->save();
            
            $action = match($request->status) {
                'active' => 'activated',
                'suspended' => 'suspended',
                'deleted' => 'deleted',
                default => 'updated'
            };
            
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
            'verified' => 'required|boolean'
        ]);
        
        try {
            $establishment = Establishment::find($id);
            
            if (!$establishment) {
                return response()->json(['success' => false, 'message' => 'Establishment not found.'], 404);
            }
            
            $establishment->verified = $request->verified;
            $establishment->save();
            
            $action = $request->verified ? 'verified' : 'unverified';
            
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
                'pickup_available' => $listing->pickup_available,
                'delivery_available' => $listing->delivery_available,
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
            $listing = FoodListing::find($id);
            
            if (!$listing) {
                return response()->json(['success' => false, 'message' => 'Food listing not found.'], 404);
            }
            
            $listing->status = $request->status;
            $listing->save();
            
            $action = match($request->status) {
                'active' => 'activated',
                'inactive' => 'disabled',
                'expired' => 'marked as expired',
                default => 'updated'
            };
            
            return response()->json([
                'success' => true,
                'message' => "Food listing {$action} successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update food listing status.',
                'error' => $e->getMessage()
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
            $listing = FoodListing::find($id);
            
            if (!$listing) {
                return response()->json(['success' => false, 'message' => 'Food listing not found.'], 404);
            }
            
            // Delete the listing
            $listing->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Food listing deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete food listing.',
                'error' => $e->getMessage()
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
            'reason' => 'required|string|max:500'
        ]);
        
        try {
            $order = Order::find($id);
            
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
            }
            
            if ($order->status === 'cancelled') {
                return response()->json(['success' => false, 'message' => 'Order is already cancelled.'], 400);
            }
            
            if ($order->status === 'completed') {
                return response()->json(['success' => false, 'message' => 'Cannot cancel a completed order.'], 400);
            }
            
            DB::beginTransaction();
            try {
                // Restore stock using StockService (idempotent)
                $stockService = new \App\Services\StockService();
                $stockResult = $stockService->restoreStock($order, 'Admin Force Cancel: ' . $request->reason);
                
                if (!$stockResult['success']) {
                    throw new \Exception($stockResult['message']);
                }
                
                $order->status = 'cancelled';
                $order->cancelled_at = now();
                $order->cancellation_reason = 'Admin Force Cancel: ' . $request->reason;
                $order->save();
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled and stock restored successfully.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel order: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order.',
                'error' => $e->getMessage()
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
                'delivery_option' => $request->delivery_option,
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
            'active_requests' => DonationRequest::whereIn('status', ['pending', 'active'])->count(),
            'completed_requests' => DonationRequest::where('status', 'completed')->count(),
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
     * Admin - Food Banks Management
     */
    public function adminFoodbanks()
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }
        
        $user = $this->getUserData();
        return view('admin.foodbanks', compact('user'));
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
            $review->flagged = !$review->flagged;
            $review->flagged_at = $review->flagged ? now() : null;
            $review->save();
            
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
}
