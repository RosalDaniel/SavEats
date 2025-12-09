<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FoodListing;
use App\Models\Establishment;
use App\Models\Order;
use App\Models\Review;
use App\Models\Announcement;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Models\Consumer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FoodListingController extends Controller
{
    /**
     * Display help center for consumers
     */
    public function help()
    {
        return view('consumer.help');
    }

    /**
     * Display settings page for consumers
     */
    public function settings()
    {
        $userData = $this->getUserData();
        return view('consumer.settings', compact('userData'));
    }

    public function myImpact()
    {
        $userData = $this->getUserData();
        
        // Get consumer ID from session
        $consumerId = session('user_id');
        
        if (!$consumerId) {
            // If no consumer ID, return with zero values and empty chart data
            $moneySaved = 0;
            $foodSaved = 0;
            $dailyData = [];
            $monthlyData = [];
            $yearlyData = [];
            return view('consumer.my-impact', compact('userData', 'moneySaved', 'foodSaved', 'dailyData', 'monthlyData', 'yearlyData'));
        }
        
        // Get all completed orders for this consumer
        $completedOrders = Order::with('foodListing')
            ->where('consumer_id', $consumerId)
            ->where('status', 'completed')
            ->get();
        
        // Calculate Money Saved: sum of (discount_amount * quantity) for all completed orders
        $moneySaved = 0;
        $foodSaved = 0;
        
        foreach ($completedOrders as $order) {
            if ($order->foodListing) {
                $foodListing = $order->foodListing;
                $originalPrice = (float) $foodListing->original_price;
                $discountPercentage = (float) ($foodListing->discount_percentage ?? 0);
                $quantity = (int) $order->quantity;
                
                // Calculate discount amount per unit
                if ($discountPercentage > 0) {
                    $discountAmountPerUnit = $originalPrice * ($discountPercentage / 100);
                    // Total money saved for this order = discount amount per unit * quantity
                    $moneySaved += $discountAmountPerUnit * $quantity;
                }
                
                // Sum quantities for Food Saved
                $foodSaved += $quantity;
            }
        }
        
        // Round money saved to 2 decimal places
        $moneySaved = round($moneySaved, 2);
        
        // Calculate chart data: Daily, Monthly, and Yearly food saved
        $dailyData = [];
        $monthlyData = [];
        $yearlyData = [];
        
        // Daily data (last 7 days)
        $dayLabels = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayOfWeek = $date->dayOfWeek; // 0=Sunday, 1=Monday, etc.
            
            $dayFoodSaved = Order::where('consumer_id', $consumerId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $date->toDateString())
                ->sum('quantity');
            
            $dailyData[] = [
                'label' => $dayLabels[$dayOfWeek],
                'value' => (int) $dayFoodSaved
            ];
        }
        
        // Monthly data (last 12 months)
        $monthLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            
            $monthFoodSaved = Order::where('consumer_id', $consumerId)
                ->where('status', 'completed')
                ->whereYear('completed_at', $date->year)
                ->whereMonth('completed_at', $date->month)
                ->sum('quantity');
            
            $monthlyData[] = [
                'label' => $monthLabels[$date->month - 1],
                'value' => (int) $monthFoodSaved
            ];
        }
        
        // Yearly data (last 5 years)
        for ($i = 4; $i >= 0; $i--) {
            $year = now()->subYears($i)->year;
            
            $yearFoodSaved = Order::where('consumer_id', $consumerId)
                ->where('status', 'completed')
                ->whereYear('completed_at', $year)
                ->sum('quantity');
            
            $yearlyData[] = [
                'label' => (string) $year,
                'value' => (int) $yearFoodSaved
            ];
        }
        
        return view('consumer.my-impact', compact(
            'userData', 
            'moneySaved', 
            'foodSaved',
            'dailyData',
            'monthlyData',
            'yearlyData'
        ));
    }

    public function announcements()
    {
        $userData = $this->getUserData();
        
        // Fetch announcements for consumers (all + consumer-specific)
        $announcements = Announcement::where('status', 'active')
            ->where(function($query) {
                $query->where('target_audience', 'all')
                      ->orWhere('target_audience', 'consumer');
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Group announcements by date
        $groupedAnnouncements = $this->groupAnnouncementsByDate($announcements);
        
        return view('consumer.announcements', compact('userData', 'announcements', 'groupedAnnouncements'));
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
     * Display the food listing page (global list for all consumers)
     */
    public function index()
    {
        // Get user data from session
        $userData = $this->getUserData();
        
        // Get real food listings from database
        $foodListings = FoodListing::with('establishment')
            ->active()
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                // Get establishment data
                $establishment = $item->establishment;
                $storeName = 'Unknown Store';
                
                if ($establishment) {
                    $storeName = $establishment->business_name ?? 
                                $establishment->owner_fname . ' ' . $establishment->owner_lname ?? 
                                'Unknown Store';
                }

                // Calculate available stock (quantity - reserved_stock)
                $availableStock = $item->quantity - ($item->reserved_stock ?? 0);
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $this->calculateDiscountedPrice($item->original_price, $item->discount_percentage),
                    'original_price' => (float) $item->original_price,
                    'discount' => $item->discount_percentage ? round($item->discount_percentage) : 0,
                    'quantity' => (string) max(0, $availableStock),
                    'category' => $item->category,
                    'store' => $storeName,
                    'image' => $item->image_url,
                    'expiry' => $item->expiry_date->format('Y-m-d'),
                    'location' => $item->address ?? 'Location not specified',
                    'pickup_available' => $item->pickup_available,
                    'delivery_available' => $item->delivery_available,
                    'establishment_id' => $item->establishment_id
                ];
            })
            ->toArray();

        return view('consumer.food-listing', compact('foodListings', 'userData'));
    }

    /**
     * Display the user's orders page (user-specific data)
     */
    public function myOrders()
    {
        // Get user data from session
        $userData = $this->getUserData();
        
        // Get real orders from database
        $consumerId = session('user_id');
        $orders = Order::with(['foodListing', 'establishment', 'review'])
            ->where('consumer_id', $consumerId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($order) {
                // Filter out orders with missing relationships
                return $order->foodListing !== null && $order->establishment !== null;
            });

        // Organize orders by status
        $userOrders = [
            'upcoming' => $orders->whereIn('status', ['pending', 'accepted', 'pending_delivery_confirmation', 'on_the_way'])->map(function ($order) {
                return [
                    'order_id' => 'ID#' . $order->id,
                    'order_id_raw' => $order->id, // For JavaScript
                    'product_name' => $order->foodListing->name,
                    'quantity' => $order->quantity,
                    'price' => $order->total_price,
                    'store_name' => $order->establishment->business_name ?? 
                        ($order->establishment->owner_fname . ' ' . $order->establishment->owner_lname),
                    'store_hours' => 'Mon - Sat | 7:00 am - 5:00 pm', // Default hours
                    'delivery_method' => ucfirst($order->delivery_method),
                    'order_date' => $order->created_at->format('Y-m-d'),
                    'pickup_time' => $order->pickup_start_time && $order->pickup_end_time ? 
                        $order->pickup_start_time . '-' . $order->pickup_end_time : 'TBD',
                    'status' => $order->status
                ];
            })->values()->toArray(),
            'completed' => $orders->where('status', 'completed')->map(function ($order) {
                return [
                    'order_id' => 'ID#' . $order->id,
                    'order_id_raw' => $order->id, // For JavaScript
                    'product_name' => $order->foodListing->name,
                    'quantity' => $order->quantity,
                    'price' => $order->total_price,
                    'store_name' => $order->establishment->business_name ?? 
                        ($order->establishment->owner_fname . ' ' . $order->establishment->owner_lname),
                    'store_hours' => 'Mon - Sat | 7:00 am - 5:00 pm', // Default hours
                    'delivery_method' => ucfirst($order->delivery_method),
                    'order_date' => $order->created_at->format('Y-m-d'),
                    'pickup_time' => $order->pickup_start_time && $order->pickup_end_time ? 
                        $order->pickup_start_time . '-' . $order->pickup_end_time : 'TBD',
                    'status' => $order->status,
                    'completed_date' => $order->completed_at ? \Carbon\Carbon::parse($order->completed_at)->setTimezone('Asia/Manila')->format('F d, Y | h:i A') : null,
                    'has_rating' => $order->review !== null,
                    'rating' => $order->review ? [
                        'rating' => $order->review->rating,
                        'description' => $order->review->description,
                        'image_path' => $order->review->image_path,
                        'video_path' => $order->review->video_path,
                        'created_at' => $order->review->created_at->format('M d, Y'),
                    ] : null
                ];
            })->values()->toArray(),
            'cancelled' => $orders->where('status', 'cancelled')->map(function ($order) {
                return [
                    'order_id' => 'ID#' . $order->id,
                    'product_name' => $order->foodListing->name,
                    'quantity' => $order->quantity,
                    'price' => $order->total_price,
                    'store_name' => $order->establishment->business_name ?? 
                        ($order->establishment->owner_fname . ' ' . $order->establishment->owner_lname),
                    'store_hours' => 'Mon - Sat | 7:00 am - 5:00 pm', // Default hours
                    'delivery_method' => ucfirst($order->delivery_method),
                    'order_date' => $order->created_at->format('Y-m-d'),
                    'pickup_time' => $order->pickup_start_time && $order->pickup_end_time ? 
                        $order->pickup_start_time . '-' . $order->pickup_end_time : 'TBD',
                    'status' => $order->status,
                    'cancelled_date' => $order->cancelled_at ? \Carbon\Carbon::parse($order->cancelled_at)->setTimezone('Asia/Manila')->format('M d, Y h:i A') : null,
                    'cancellation_reason' => $order->cancellation_reason ?? 'No reason provided'
                ];
            })->values()->toArray()
        ];

        return view('consumer.my-orders', compact('userOrders', 'userData'));
    }

    /**
     * Display a specific food item detail page
     */
    public function show($id)
    {
        // Get user data from session
        $userData = $this->getUserData();
        
        // Get the specific food listing with establishment relationship
        $foodListing = FoodListing::with('establishment')->findOrFail($id);
        
        // Prevent access to inactive items for non-admin users
        if ($foodListing->status !== 'active' && session('user_type') !== 'admin') {
            return redirect()->route('food-listing.index')->with('error', 'This food item is no longer available.');
        }
        
        // Get establishment data
        $establishment = $foodListing->establishment;
        $storeName = 'Unknown Store';
        
        if ($establishment) {
            $storeName = $establishment->business_name ?? 
                        $establishment->owner_fname . ' ' . $establishment->owner_lname ?? 
                        'Unknown Store';
        }

        // Calculate available stock (quantity - reserved_stock)
        $availableStock = $foodListing->quantity - ($foodListing->reserved_stock ?? 0);
        
        // Get reviews for this food listing
        $reviewsData = Review::with('consumer')
            ->where('food_listing_id', $foodListing->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate average rating and total reviews
        $totalReviews = $reviewsData->count();
        $averageRating = $totalReviews > 0 
            ? round($reviewsData->avg('rating'), 1) 
            : 0;

        // Format reviews for display
        $reviews = $reviewsData->map(function ($review) {
            $consumer = $review->consumer;
            $userName = 'Anonymous';
            
            if ($consumer) {
                $fname = trim($consumer->fname ?? '');
                $lname = trim($consumer->lname ?? '');
                $userName = trim($fname . ' ' . $lname) ?: 'Anonymous';
            }

            return [
                'id' => $review->id,
                'user_name' => $userName,
                'avatar' => null, // Will use initials fallback
                'rating' => $review->rating,
                'comment' => $review->description ?? '',
                'date' => $review->created_at->format('Y-m-d'),
                'image_path' => $review->image_path ? Storage::url($review->image_path) : null,
                'video_path' => $review->video_path ? Storage::url($review->video_path) : null,
                'flagged' => $review->flagged ?? false,
            ];
        })->toArray();

        // Format the data for the view
        $foodItem = [
            'id' => $foodListing->id,
            'name' => $foodListing->name,
            'description' => $foodListing->description,
            'price' => $this->calculateDiscountedPrice($foodListing->original_price, $foodListing->discount_percentage),
            'original_price' => $foodListing->original_price,
            'discount' => $foodListing->discount_percentage ? round($foodListing->discount_percentage) : 0,
            'quantity' => (string) max(0, $availableStock),
            'category' => $foodListing->category,
            'store' => $storeName,
            'image' => $foodListing->image_url,
            'expiry' => $foodListing->expiry_date->format('Y-m-d'),
            'expiry_formatted' => $foodListing->expiry_date->format('F j, Y'),
            'location' => $foodListing->address ?? 'Location not specified',
            'pickup_available' => $foodListing->pickup_available,
            'delivery_available' => $foodListing->delivery_available,
            'establishment_id' => $foodListing->establishment_id,
            'operating_hours' => 'Mon - Sat | 7:00 am - 5:00 pm', // This would come from establishment settings
            'rating' => $averageRating,
            'total_reviews' => $totalReviews,
        ];

        return view('consumer.food-detail', compact('foodItem', 'reviews', 'userData'));
    }

    /**
     * Get user data from session
     */
    private function getUserData()
    {
        $userId = session('user_id');
        $userType = session('user_type');
        
        if (!$userId || !$userType) {
            return null;
        }

        // Get user data from appropriate model based on user type
        switch ($userType) {
            case 'consumer':
                return Consumer::find($userId);
            case 'establishment':
                return \App\Models\Establishment::find($userId);
            case 'foodbank':
                return \App\Models\Foodbank::find($userId);
            case 'admin':
                return \App\Models\User::find($userId);
            default:
                return null;
        }
    }

    /**
     * Display order confirmation page
     */
    public function orderConfirmation(Request $request)
    {
        // Get user data from session
        $userData = $this->getUserData();
        
        // Get product ID and quantity from URL parameters
        $productId = $request->get('id');
        $quantity = $request->get('quantity', 1);
        
        // If no product ID provided, redirect to food listing
        if (!$productId) {
            return redirect()->route('consumer.food-listing');
        }
        
        // Fetch the food item with establishment details
        $foodItem = FoodListing::with('establishment')->find($productId);
        
        // If food item not found, redirect to food listing
        if (!$foodItem) {
            return redirect()->route('consumer.food-listing');
        }
        
        // Calculate pricing
        $originalPrice = (float) $foodItem->original_price;
        $discountPercentage = (float) $foodItem->discount_percentage;
        
        // Calculate discounted price properly
        if ($discountPercentage > 0) {
            $discountAmount = ($originalPrice * $discountPercentage) / 100;
            $discountedPrice = $originalPrice - $discountAmount;
        } else {
            $discountedPrice = $originalPrice; // No discount, use original price
        }
        
        // Get establishment name with fallback
        $establishmentName = $foodItem->establishment->business_name ?? 
                           ($foodItem->establishment->owner_fname . ' ' . $foodItem->establishment->owner_lname) ?? 
                           'Unknown Store';
        
        // Get establishment address - prefer formatted_address from pinned location
        $establishmentAddress = $foodItem->establishment->formatted_address ?? 
                              $foodItem->establishment->address ?? 
                              $foodItem->address ?? 
                              'Location not specified';
        
        // Get store coordinates - use Cebu City center as default if not available
        // Cebu City coordinates: 10.3157, 123.8854
        $storeLat = $foodItem->establishment->latitude ?? 10.3157;
        $storeLng = $foodItem->establishment->longitude ?? 123.8854;
        
        // Ensure coordinates are valid numbers
        $storeLat = is_numeric($storeLat) ? (float) $storeLat : 10.3157;
        $storeLng = is_numeric($storeLng) ? (float) $storeLng : 123.8854;
        
        return view('consumer.order-confirmation', compact(
            'foodItem', 
            'quantity', 
            'originalPrice', 
            'discountedPrice', 
            'discountPercentage',
            'establishmentName',
            'establishmentAddress',
            'storeLat',
            'storeLng',
            'userData'
        ));
    }

    public function paymentOptions(Request $request)
    {
        $userData = $this->getUserData();
        $productId = $request->get('id');
        $quantity = $request->get('quantity', 1);
        $receiveMethod = $request->get('method', 'pickup');
        $phoneNumber = $request->get('phone', '');
        $startTime = $request->get('startTime', '');
        $endTime = $request->get('endTime', '');
        
        // Delivery data
        $deliveryAddress = $request->get('deliveryAddress', '');
        $deliveryLat = $request->get('deliveryLat');
        $deliveryLng = $request->get('deliveryLng');
        $deliveryDistance = $request->get('deliveryDistance');
        $deliveryFee = $request->get('deliveryFee', 0);
        $deliveryETA = $request->get('deliveryETA', '');
        $deliveryInstructions = $request->get('deliveryInstructions', '');
        $fullName = $request->get('fullName', '');
        
        if (!$productId) {
            return redirect()->route('consumer.food-listing');
        }
        
        $foodItem = FoodListing::with('establishment')->find($productId);
        
        if (!$foodItem) {
            return redirect()->route('consumer.food-listing');
        }
        
        $originalPrice = (float) $foodItem->original_price;
        $discountPercentage = (float) $foodItem->discount_percentage;
        $discountedPrice = $this->calculateDiscountedPrice($originalPrice, $discountPercentage);
        
        $establishmentName = $foodItem->establishment->business_name ?? 
                           ($foodItem->establishment->owner_fname . ' ' . $foodItem->establishment->owner_lname) ?? 
                           'Unknown Store';
        
        $establishmentAddress = $foodItem->establishment->address ?? 
                              $foodItem->address ?? 
                              'Location not specified';
        
        // Calculate prices
        $unitPrice = $discountedPrice > 0 ? $discountedPrice : $originalPrice;
        $subtotal = $unitPrice * $quantity;
        // Delivery fee is informational estimate only - NOT included in total
        $deliveryFeeAmount = 0.00; // Always 0 - fee is estimate only
        $total = $subtotal; // Total does NOT include delivery fee
        
        return view('consumer.payment-options', compact(
            'foodItem', 
            'quantity', 
            'originalPrice', 
            'discountedPrice', 
            'discountPercentage',
            'establishmentName',
            'establishmentAddress',
            'userData',
            'receiveMethod',
            'phoneNumber',
            'startTime',
            'endTime',
            'unitPrice',
            'subtotal',
            'deliveryFeeAmount',
            'deliveryAddress',
            'deliveryLat',
            'deliveryLng',
            'deliveryDistance',
            'deliveryFee',
            'deliveryETA',
            'deliveryInstructions',
            'fullName',
            'total'
        ));
    }

    /**
     * Place order from payment options page
     */
    /**
     * Proxy endpoint for Nominatim geocoding to handle CORS
     */
    public function geocodeAddress(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query) || strlen($query) < 3) {
            return response()->json(['error' => 'Query too short'], 400);
        }
        
        // Rate limiting: max 1 request per second per IP
        $key = 'nominatim_' . $request->ip();
        if (Cache::has($key)) {
            return response()->json(['error' => 'Rate limit exceeded. Please wait a moment.'], 429);
        }
        Cache::put($key, true, 1); // 1 second cache
        
        try {
            // Improve query format - try with and without "Philippines" suffix
            $queryFormatted = trim($query);
            if (!preg_match('/philippines|ph|cebu/i', $queryFormatted)) {
                $queryFormatted .= ', Cebu, Philippines';
            }
            
            $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . 
                   urlencode($queryFormatted) . 
                   '&countrycodes=ph&limit=10&addressdetails=1&dedupe=1';
            
            \Log::info('Nominatim geocoding request: ' . $url);
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'SavEats Application/1.0 (contact: admin@saveats.com)',
                    'Accept' => 'application/json',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get($url);
            
            if ($response->successful()) {
                $results = $response->json();
                // Ensure we return an array, even if empty
                if (!is_array($results)) {
                    \Log::warning('Nominatim returned non-array response: ' . json_encode($results));
                    return response()->json([]);
                }
                \Log::info('Nominatim geocoding success: ' . count($results) . ' results for query: ' . $query);
                return response()->json($results);
            } else {
                \Log::error('Nominatim API error: ' . $response->status() . ' - ' . $response->body());
                return response()->json(['error' => 'Geocoding service error'], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Nominatim geocoding error: ' . $e->getMessage());
            return response()->json(['error' => 'Geocoding service unavailable: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Proxy endpoint for Nominatim reverse geocoding
     */
    public function reverseGeocode(Request $request)
    {
        $lat = $request->get('lat');
        $lng = $request->get('lng');
        
        if (empty($lat) || empty($lng)) {
            return response()->json(['error' => 'Coordinates required'], 400);
        }
        
        try {
            $url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . 
                   urlencode($lat) . '&lon=' . urlencode($lng) . 
                   '&zoom=18&addressdetails=1';
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'SavEats Application (contact: ' . config('app.email', 'admin@saveats.com') . ')',
                    'Accept' => 'application/json',
                ])
                ->get($url);
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json(['error' => 'Reverse geocoding service error'], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Nominatim reverse geocoding error: ' . $e->getMessage());
            return response()->json(['error' => 'Reverse geocoding service unavailable'], 500);
        }
    }

    public function placeOrder(Request $request)
    {
        // Validate request and return JSON errors if validation fails
        $validator = \Validator::make($request->all(), [
            'food_listing_id' => 'required|exists:food_listings,id',
            'quantity' => 'required|integer|min:1',
            'delivery_method' => 'required|in:pickup,delivery',
            'delivery_type' => 'nullable|in:pickup,delivery',
            'payment_method' => 'required|in:cash,card,ewallet',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_lat' => 'nullable|numeric|between:-90,90',
            'delivery_lng' => 'nullable|numeric|between:-180,180',
            'delivery_distance' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'delivery_eta' => 'nullable|string|max:50',
            'delivery_instructions' => 'nullable|string|max:1000',
            'pickup_start_time' => 'nullable|string|max:5',
            'pickup_end_time' => 'nullable|string|max:5',
        ]);

        if ($validator->fails()) {
            \Log::info('Order validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $foodItem = FoodListing::with('establishment')->find($request->food_listing_id);
            
            if (!$foodItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Food item not found'
                ], 404);
            }

            // Get consumer from session
            $consumerId = session('user_id');
            if (!$consumerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Check if sufficient quantity is available (available stock = quantity - reserved_stock)
            // Use row-level locking to get accurate stock
            $availableStock = DB::transaction(function () use ($foodItem) {
                $lockedItem = FoodListing::lockForUpdate()->find($foodItem->id);
                return $lockedItem->quantity - ($lockedItem->reserved_stock ?? 0);
            });
            
            if ($availableStock < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient quantity available. Only ' . $availableStock . ' items available.'
                ], 400);
            }

            // Calculate prices
            $originalPrice = (float) $foodItem->original_price;
            $discountPercentage = (float) $foodItem->discount_percentage;
            $unitPrice = $this->calculateDiscountedPrice($originalPrice, $discountPercentage);
            $subtotal = $unitPrice * $request->quantity;
            
            // Delivery fee is informational estimate only - NOT included in order total
            $deliveryFee = 0; // Always 0 - fee is estimate only, not charged
            $totalPrice = $subtotal; // Total does NOT include delivery fee

            // Use database transaction to ensure atomicity
            DB::beginTransaction();
            try {
                // Create order
                $orderData = [
                    'order_number' => Order::generateOrderNumber(),
                    'consumer_id' => $consumerId,
                    'establishment_id' => $foodItem->establishment_id,
                    'food_listing_id' => $foodItem->id,
                    'quantity' => $request->quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'delivery_method' => $request->delivery_method,
                    'delivery_type' => $request->delivery_type ?? $request->delivery_method,
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'confirmed', // Payment confirmed at order placement (immediate payment)
                    'status' => $request->delivery_method === 'delivery' ? 'pending_delivery_confirmation' : 'pending',
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'pickup_start_time' => $this->formatTimeForDatabase($request->pickup_start_time),
                    'pickup_end_time' => $this->formatTimeForDatabase($request->pickup_end_time),
                ];
                
                // Add delivery fields if delivery method
                if ($request->delivery_method === 'delivery') {
                    // Validate required delivery fields
                    if (empty($request->delivery_address)) {
                        throw new \Exception('Delivery address is required');
                    }
                    if (empty($request->delivery_lat) || empty($request->delivery_lng)) {
                        throw new \Exception('Delivery coordinates are required');
                    }
                    
                    $orderData['delivery_address'] = $request->delivery_address;
                    $orderData['delivery_lat'] = (float) $request->delivery_lat;
                    $orderData['delivery_lng'] = (float) $request->delivery_lng;
                    $orderData['delivery_distance'] = $request->delivery_distance ? (float) $request->delivery_distance : null;
                    $orderData['delivery_fee'] = 0; // Delivery fee is informational estimate only - not stored
                    $orderData['delivery_eta'] = $request->delivery_eta;
                    $orderData['delivery_instructions'] = $request->delivery_instructions;
                }
                
                $order = Order::create($orderData);

                // Stock will be deducted when establishment accepts the order
                // No stock deduction at order placement - order is pending acceptance
                
                DB::commit();
                
                // Reload order with relationships for notification
                $order->load(['establishment', 'foodListing', 'consumer']);
                
                // Send notification to establishment
                try {
                    NotificationService::notifyOrderPlaced($order);
                } catch (\Exception $e) {
                    // Log error but don't fail the order
                    \Log::error('Failed to create notification for order: ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                DB::rollBack();
                // Order creation failed - no stock was deducted, so nothing to restore
                throw $e;
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Order placement error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get consumer orders for my-orders page
     */
    public function getConsumerOrders()
    {
        $consumerId = session('user_id');
        if (!$consumerId) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $orders = Order::with(['foodListing', 'establishment'])
            ->where('consumer_id', $consumerId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'product_name' => $order->foodListing->name,
                    'quantity' => $order->quantity . ' pcs.',
                    'price' => $order->total_price,
                    'status' => $order->status,
                    'delivery_method' => ucfirst($order->delivery_method),
                    'payment_method' => ucfirst($order->payment_method),
                    'created_at' => $order->created_at->format('M d, Y H:i'),
                    'establishment_name' => $order->establishment->business_name ?? 
                        ($order->establishment->owner_fname . ' ' . $order->establishment->owner_lname),
                ];
            });

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }

    /**
     * Get order details for consumer receipt modal
     */
    public function getOrderDetails($id)
    {
        $consumerId = session('user_id');
        if (!$consumerId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $order = Order::with(['foodListing', 'establishment'])
            ->where('id', $id)
            ->where('consumer_id', $consumerId)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $userData = $this->getUserData();

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'effective_status' => $order->effective_status,
                'created_at' => \Carbon\Carbon::parse($order->created_at)->setTimezone('Asia/Manila')->format('F d, Y | h:i A'),
                'accepted_at' => $order->accepted_at ? \Carbon\Carbon::parse($order->accepted_at)->setTimezone('Asia/Manila')->format('F d, Y | h:i A') : null,
                'out_for_delivery_at' => $order->out_for_delivery_at ? \Carbon\Carbon::parse($order->out_for_delivery_at)->setTimezone('Asia/Manila')->format('F d, Y | h:i A') : null,
                'completed_at' => $order->completed_at ? \Carbon\Carbon::parse($order->completed_at)->setTimezone('Asia/Manila')->format('F d, Y | h:i A') : null,
                'cancelled_at' => $order->cancelled_at ? \Carbon\Carbon::parse($order->cancelled_at)->setTimezone('Asia/Manila')->format('F d, Y | h:i A') : null,
                'payment_method' => ucfirst($order->payment_method),
                'delivery_method' => ucfirst($order->delivery_method),
                'customer_name' => urldecode($order->customer_name ?? ''),
                'customer_phone' => $order->customer_phone,
                'customer_email' => $userData->email ?? 'N/A',
                'delivery_address' => $order->delivery_address,
                'pickup_start_time' => $order->pickup_start_time ? (is_string($order->pickup_start_time) ? substr($order->pickup_start_time, 0, 5) : $order->pickup_start_time->format('H:i')) : null,
                'pickup_end_time' => $order->pickup_end_time ? (is_string($order->pickup_end_time) ? substr($order->pickup_end_time, 0, 5) : $order->pickup_end_time->format('H:i')) : null,
                'items' => [
                    [
                        'name' => $order->foodListing->name,
                        'quantity' => $order->quantity,
                        'unit_price' => (float) $order->unit_price,
                        'total_price' => (float) $order->total_price,
                    ]
                ],
                'subtotal' => (float) $order->total_price,
                'delivery_fee' => 0.00,
                'total' => (float) $order->total_price,
                'store_name' => $order->establishment->business_name ?? ($order->establishment->owner_fname . ' ' . $order->establishment->owner_lname),
                'store_address' => $order->establishment->address ?? $order->foodListing->address ?? 'N/A',
            ]
        ]);
    }

    /**
     * Confirm delivery for consumer
     */
    public function confirmDelivery(Request $request, $id)
    {
        $consumerId = session('user_id');
        if (!$consumerId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $order = Order::with(['foodListing', 'establishment'])
            ->where('id', $id)
            ->where('consumer_id', $consumerId)
            ->where('delivery_method', 'delivery')
            ->whereIn('status', ['on_the_way', 'pending_delivery_confirmation'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => 'Order not found or cannot be confirmed'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Calculate 5% platform fee and net earnings
            $platformFeeRate = 0.05; // 5%
            $platformFee = round($order->total_price * $platformFeeRate, 2);
            $netEarnings = round($order->total_price - $platformFee, 2);

            $order->status = 'completed';
            $order->completed_at = now('Asia/Manila');
            $order->platform_fee = $platformFee;
            $order->net_earnings = $netEarnings;
            $order->save();

            // Reload order with relationships for notification
            $order->load(['consumer', 'establishment', 'foodListing']);

            DB::commit();

            // Send notification to establishment
            \App\Services\NotificationService::notifyOrderCompleted($order);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Failed to confirm delivery: ' . $e->getMessage(),
                'message' => 'Failed to confirm delivery: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Delivery confirmed successfully. Thank you for your order!'
        ]);
    }

    /**
     * Submit a review/rating for an order
     */
    public function submitReview(Request $request)
    {
        if (!session('user_id') || session('user_type') !== 'consumer') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = \Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'video' => 'nullable|mimes:mp4,avi,mov|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $consumerId = session('user_id');
        $order = Order::with(['foodListing', 'establishment'])
            ->where('id', $request->order_id)
            ->where('consumer_id', $consumerId)
            ->where('status', 'completed')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or cannot be reviewed'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $imagePath = null;
            $videoPath = null;
            $existingReview = $order->review;
            $isUpdate = $existingReview !== null;
            
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($existingReview && $existingReview->image_path) {
                    Storage::disk('public')->delete($existingReview->image_path);
                }
                $image = $request->file('image');
                $imagePath = $image->store('reviews/images', 'public');
            } elseif ($existingReview) {
                // Keep existing image if not uploading new one
                $imagePath = $existingReview->image_path;
            } else {
                // New review with no image
                $imagePath = null;
            }

            // Handle video upload
            if ($request->hasFile('video')) {
                // Delete old video if exists
                if ($existingReview && $existingReview->video_path) {
                    Storage::disk('public')->delete($existingReview->video_path);
                }
                $video = $request->file('video');
                $videoPath = $video->store('reviews/videos', 'public');
            } elseif ($existingReview) {
                // Keep existing video if not uploading new one
                $videoPath = $existingReview->video_path;
            } else {
                // New review with no video
                $videoPath = null;
            }

            if ($isUpdate) {
                // Update existing review - preserve created_at
                $originalCreatedAt = $existingReview->created_at;
                $existingReview->update([
                    'rating' => $request->rating,
                    'description' => $request->description,
                    'image_path' => $imagePath,
                    'video_path' => $videoPath,
                ]);
                // Restore original created_at timestamp
                $existingReview->created_at = $originalCreatedAt;
                $existingReview->save();
                $review = $existingReview;
            } else {
                // Create new review
                $review = Review::create([
                    'order_id' => $order->id,
                    'consumer_id' => $consumerId,
                    'food_listing_id' => $order->food_listing_id,
                    'establishment_id' => $order->establishment_id,
                    'rating' => $request->rating,
                    'description' => $request->description,
                    'image_path' => $imagePath,
                    'video_path' => $videoPath,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isUpdate ? 'Review updated successfully' : 'Review submitted successfully',
                'review' => [
                    'rating' => $review->rating,
                    'description' => $review->description,
                    'image_path' => $review->image_path ? Storage::url($review->image_path) : null,
                    'video_path' => $review->video_path ? Storage::url($review->video_path) : null,
                    'created_at' => $review->created_at->format('M d, Y'),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel an order (consumer)
     */
    public function cancelOrder(Request $request, $id)
    {
        if (!session('user_id') || session('user_type') !== 'consumer') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $consumerId = session('user_id');
        $order = Order::with('foodListing')
            ->where('id', $id)
            ->where('consumer_id', $consumerId)
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or cannot be cancelled'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Only restore stock if it was actually deducted (i.e., order was accepted)
            // If order is still pending, no stock was deducted, so nothing to restore
            if ($order->stock_deducted) {
                $stockService = new \App\Services\StockService();
                $stockResult = $stockService->restoreStock($order, $request->input('reason', 'Cancelled by customer'));
                
                // Log the result but don't fail if stock wasn't deducted
                if (!$stockResult['success'] && strpos($stockResult['message'], 'already restored') === false) {
                    // Only throw if it's a real error, not if stock was never deducted
                    if (strpos($stockResult['message'], 'never deducted') === false) {
                        throw new \Exception($stockResult['message']);
                    }
                }
            }

            // Update order status
            $order->status = 'cancelled';
            $order->cancelled_at = now();
            $order->cancellation_reason = $request->input('reason', 'Cancelled by customer');
            $order->save();
            
            // Reload order with relationships for notification
            $order->load(['establishment', 'consumer']);

            DB::commit();
            
            // Send notification to establishment
            NotificationService::notifyOrderCancelled($order, 'consumer');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully'
        ]);
    }

    /**
     * Get review for an order
     */
    public function getReview($orderId)
    {
        if (!session('user_id') || session('user_type') !== 'consumer') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $consumerId = session('user_id');
        $order = Order::with('review')
            ->where('id', $orderId)
            ->where('consumer_id', $consumerId)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        if (!$order->review) {
            return response()->json([
                'success' => false,
                'message' => 'No review found for this order'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'review' => [
                'rating' => $order->review->rating,
                'description' => $order->review->description,
                'image_path' => $order->review->image_path ? Storage::url($order->review->image_path) : null,
                'video_path' => $order->review->video_path ? Storage::url($order->review->video_path) : null,
                'created_at' => $order->review->created_at->format('M d, Y'),
            ]
        ]);
    }

    /**
     * Format time string for database storage
     */
    private function formatTimeForDatabase($timeString)
    {
        if (empty($timeString)) {
            return null;
        }

        // Handle URL-encoded time (e.g., "15%3A00" -> "15:00")
        $timeString = urldecode($timeString);
        
        // Validate time format (HH:MM or H:MM)
        if (preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $timeString)) {
            // Convert to H:i:s format for database
            return date('H:i:s', strtotime($timeString));
        }
        
        // If format is invalid, return null
        return null;
    }

    /**
     * Calculate discounted price based on original price and discount percentage
     */
    private function calculateDiscountedPrice($originalPrice, $discountPercentage)
    {
        $originalPrice = (float) $originalPrice;
        $discountPercentage = (float) $discountPercentage;
        
        if ($discountPercentage > 0) {
            $discountAmount = ($originalPrice * $discountPercentage) / 100;
            return $originalPrice - $discountAmount;
        }
        
        return $originalPrice; // No discount, return original price
    }
}
