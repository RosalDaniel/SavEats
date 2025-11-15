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
            'ratedOrdersCount'
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
        return view('foodbank.announcements', compact('user'));
    }

    /**
     * Show help center for foodbank
     */
    public function foodbankHelp()
    {
        return view('foodbank.help');
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
        return view('admin.dashboard', compact('user'));
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
