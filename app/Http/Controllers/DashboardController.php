<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consumer;
use App\Models\Establishment;
use App\Models\Foodbank;
use App\Models\User;
use App\Models\FoodListing;
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
        $user = $this->getUserData();
        
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
        
        return view('consumer.dashboard', compact('user', 'bestDeals'));
    }

    /**
     * Show establishment dashboard
     */
    public function establishment()
    {
        $user = $this->getUserData();
        return view('establishment.dashboard', compact('user'));
    }

    /**
     * Show foodbank dashboard
     */
    public function foodbank()
    {
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
     * Show admin dashboard
     */
    public function admin()
    {
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
