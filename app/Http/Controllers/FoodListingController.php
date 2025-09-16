<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FoodListing;
use App\Models\Establishment;
use Illuminate\Http\Request;
use App\Models\Consumer;
use Illuminate\Support\Facades\Storage;

class FoodListingController extends Controller
{
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
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->discounted_price ?? $item->original_price,
                    'original_price' => $item->original_price,
                    'discount' => $item->discount_percentage ? round($item->discount_percentage) : 0,
                    'quantity' => (string) $item->quantity,
                    'category' => $item->category,
                    'store' => $item->establishment->business_name ?? 'Unknown Store',
                    'image' => $item->image_path ? Storage::url($item->image_path) : 'https://via.placeholder.com/300x200/4a7c59/ffffff?text=' . strtoupper(substr($item->name, 0, 1)),
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
        
        // Sample user orders data organized by status (in a real app, this would come from database)
        $userOrders = [
            'upcoming' => [
                [
                    'order_id' => 'ID#12323',
                    'product_name' => 'Banana Bread',
                    'quantity' => '10',
                    'price' => 187.00,
                    'store_name' => 'Joy Share Grocery',
                    'store_hours' => 'Mon - Sat | 7:00 am - 5:00 pm',
                    'delivery_method' => 'Pick-Up',
                    'order_date' => '2024-01-20',
                    'pickup_time' => '14:00-16:00'
                ],
                [
                    'order_id' => 'ID#12324',
                    'product_name' => 'Banana Bread',
                    'quantity' => '10',
                    'price' => 187.00,
                    'store_name' => 'Joy Share Grocery',
                    'store_hours' => 'Mon - Sat | 7:00 am - 5:00 pm',
                    'delivery_method' => 'Pick-Up',
                    'order_date' => '2024-01-20',
                    'pickup_time' => '14:00-16:00'
                ],
                [
                    'order_id' => 'ID#12325',
                    'product_name' => 'Banana Bread',
                    'quantity' => '10',
                    'price' => 187.00,
                    'store_name' => 'Joy Share Grocery',
                    'store_hours' => 'Mon - Sat | 7:00 am - 5:00 pm',
                    'delivery_method' => 'Pick-Up',
                    'order_date' => '2024-01-20',
                    'pickup_time' => '14:00-16:00'
                ]
            ],
            'completed' => [
                [
                    'order_id' => 'ID#12320',
                    'product_name' => 'Fresh Bread Loaves',
                    'quantity' => '5',
                    'price' => 125.00,
                    'store_name' => 'Green Market',
                    'store_hours' => 'Mon - Sun | 6:00 am - 8:00 pm',
                    'delivery_method' => 'Pick-Up',
                    'completed_date' => '2024-01-18',
                    'order_date' => '2024-01-17'
                ]
            ],
            'cancelled' => [
                [
                    'order_id' => 'ID#12315',
                    'product_name' => 'Mixed Vegetables',
                    'quantity' => '3',
                    'price' => 75.00,
                    'store_name' => 'Fresh Market',
                    'store_hours' => 'Mon - Fri | 8:00 am - 6:00 pm',
                    'delivery_method' => 'Pick-Up',
                    'cancelled_date' => '2024-01-15',
                    'cancellation_reason' => 'Store closed unexpectedly'
                ]
            ]
        ];

        return view('consumer.my-orders', compact('userOrders', 'userData'));
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
}
