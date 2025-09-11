<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Consumer;

class FoodListingController extends Controller
{
    /**
     * Display the food listing page (global list for all consumers)
     */
    public function index()
    {
        // Get user data from session
        $userData = $this->getUserData();
        
        // Sample food listings data (in a real app, this would come from database)
        $foodListings = [
            [
                'id' => 1,
                'name' => 'Fresh Bread Loaves',
                'description' => 'Day-old bread, still fresh and perfect for toast',
                'price' => 25.00,
                'original_price' => 35.00,
                'discount' => 29,
                'quantity' => '10 pcs',
                'category' => 'bakery',
                'store' => 'Joy Bakery',
                'image' => '/images/bread.jpg',
                'expiry' => '2024-01-25',
                'location' => 'Downtown Manila'
            ],
            [
                'id' => 2,
                'name' => 'Mixed Vegetables',
                'description' => 'Fresh vegetables from local farms',
                'price' => 50.00,
                'original_price' => 75.00,
                'discount' => 33,
                'quantity' => '2 kg',
                'category' => 'grocery',
                'store' => 'Green Market',
                'image' => '/images/vegetables.jpg',
                'expiry' => '2024-01-26',
                'location' => 'Quezon City'
            ],
            [
                'id' => 3,
                'name' => 'Pasta Dishes',
                'description' => 'Leftover pasta from restaurant, still good',
                'price' => 80.00,
                'original_price' => 120.00,
                'discount' => 33,
                'quantity' => '3 servings',
                'category' => 'restaurant',
                'store' => 'Mama Mia Restaurant',
                'image' => '/images/pasta.jpg',
                'expiry' => '2024-01-24',
                'location' => 'Makati City'
            ]
        ];

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
                    'quantity' => '10 pcs.',
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
                    'quantity' => '10 pcs.',
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
                    'quantity' => '10 pcs.',
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
                    'quantity' => '5 pcs.',
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
                    'quantity' => '3 kg',
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
