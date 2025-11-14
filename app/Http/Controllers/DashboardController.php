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
use Illuminate\Support\Facades\Storage;

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
        return view('foodbank.dashboard', compact('user'));
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
        
        // Sample data - replace with actual database queries when ready
        $donationRequests = [
            ['id' => 1, 'foodType' => 'Joy Bread', 'quantity' => 12, 'matches' => 2, 'status' => 'pending'],
            ['id' => 2, 'foodType' => 'Joy Bread', 'quantity' => 12, 'matches' => 1, 'status' => 'active'],
            ['id' => 3, 'foodType' => 'Joy Bread', 'quantity' => 12, 'matches' => 6, 'status' => 'completed'],
            ['id' => 4, 'foodType' => 'Joy Bread', 'quantity' => 12, 'matches' => 10, 'status' => 'expired'],
            ['id' => 5, 'foodType' => 'Vegetables', 'quantity' => 25, 'matches' => 3, 'status' => 'active'],
            ['id' => 6, 'foodType' => 'Canned Goods', 'quantity' => 50, 'matches' => 8, 'status' => 'pending'],
            ['id' => 7, 'foodType' => 'Fresh Fruits', 'quantity' => 30, 'matches' => 5, 'status' => 'active'],
        ];
        
        return view('foodbank.donation-request', compact('user', 'donationRequests'));
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
        
        // Sample partner data - replace with actual database queries when ready
        $partners = [
            ['id' => 1, 'name' => 'Joy Grocery Store', 'type' => 'grocery', 'location' => '31 Luna Street, Cebu City', 'rating' => 4.8, 'donations' => 45, 'impact' => 120],
            ['id' => 2, 'name' => 'Sunrise Bakery', 'type' => 'bakery', 'location' => '12 Osmeña Blvd, Cebu City', 'rating' => 4.6, 'donations' => 38, 'impact' => 95],
            ['id' => 3, 'name' => 'Green Valley Farm', 'type' => 'farm', 'location' => 'Talamban, Cebu City', 'rating' => 4.9, 'donations' => 52, 'impact' => 156],
            ['id' => 4, 'name' => 'Metro Supermarket', 'type' => 'grocery', 'location' => 'Ayala Center, Cebu City', 'rating' => 4.7, 'donations' => 61, 'impact' => 183],
            ['id' => 5, 'name' => 'Golden Bread House', 'type' => 'bakery', 'location' => 'Colon Street, Cebu City', 'rating' => 4.5, 'donations' => 29, 'impact' => 87],
            ['id' => 6, 'name' => 'Fresh Harvest Cafe', 'type' => 'restaurant', 'location' => 'IT Park, Cebu City', 'rating' => 4.8, 'donations' => 33, 'impact' => 99],
            ['id' => 7, 'name' => 'City Market', 'type' => 'grocery', 'location' => 'Carbon Market, Cebu City', 'rating' => 4.4, 'donations' => 41, 'impact' => 123],
            ['id' => 8, 'name' => 'Artisan Bakeshop', 'type' => 'bakery', 'location' => 'Banilad, Cebu City', 'rating' => 4.7, 'donations' => 35, 'impact' => 105],
            ['id' => 9, 'name' => 'Organic Roots Farm', 'type' => 'farm', 'location' => 'Busay, Cebu City', 'rating' => 4.9, 'donations' => 48, 'impact' => 144],
            ['id' => 10, 'name' => 'Daily Groceries', 'type' => 'grocery', 'location' => 'Mabolo, Cebu City', 'rating' => 4.6, 'donations' => 37, 'impact' => 111],
            ['id' => 11, 'name' => 'The Bread Corner', 'type' => 'bakery', 'location' => 'Mandaue City', 'rating' => 4.8, 'donations' => 42, 'impact' => 126],
            ['id' => 12, 'name' => 'Seaside Restaurant', 'type' => 'restaurant', 'location' => 'SRP, Cebu City', 'rating' => 4.5, 'donations' => 28, 'impact' => 84],
        ];
        
        return view('foodbank.partner-network', compact('user', 'partners'));
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
