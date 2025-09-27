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
                // Get establishment data
                $establishment = $item->establishment;
                $storeName = 'Unknown Store';
                
                if ($establishment) {
                    $storeName = $establishment->business_name ?? 
                                $establishment->owner_fname . ' ' . $establishment->owner_lname ?? 
                                'Unknown Store';
                }

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => (float) ($item->discounted_price ?? $item->original_price),
                    'original_price' => (float) $item->original_price,
                    'discount' => $item->discount_percentage ? round($item->discount_percentage) : 0,
                    'quantity' => (string) $item->quantity,
                    'category' => $item->category,
                    'store' => $storeName,
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
     * Display a specific food item detail page
     */
    public function show($id)
    {
        // Get user data from session
        $userData = $this->getUserData();
        
        // Get the specific food listing with establishment relationship
        $foodListing = FoodListing::with('establishment')->findOrFail($id);
        
        // Get establishment data
        $establishment = $foodListing->establishment;
        $storeName = 'Unknown Store';
        
        if ($establishment) {
            $storeName = $establishment->business_name ?? 
                        $establishment->owner_fname . ' ' . $establishment->owner_lname ?? 
                        'Unknown Store';
        }

        // Format the data for the view
        $foodItem = [
            'id' => $foodListing->id,
            'name' => $foodListing->name,
            'description' => $foodListing->description,
            'price' => $foodListing->discounted_price ?? $foodListing->original_price,
            'original_price' => $foodListing->original_price,
            'discount' => $foodListing->discount_percentage ? round($foodListing->discount_percentage) : 0,
            'quantity' => (string) $foodListing->quantity,
            'category' => $foodListing->category,
            'store' => $storeName,
            'image' => $foodListing->image_path ? Storage::url($foodListing->image_path) : 'https://via.placeholder.com/400x300/4a7c59/ffffff?text=' . strtoupper(substr($foodListing->name, 0, 1)),
            'expiry' => $foodListing->expiry_date->format('Y-m-d'),
            'expiry_formatted' => $foodListing->expiry_date->format('F j, Y'),
            'location' => $foodListing->address ?? 'Location not specified',
            'pickup_available' => $foodListing->pickup_available,
            'delivery_available' => $foodListing->delivery_available,
            'establishment_id' => $foodListing->establishment_id,
            'operating_hours' => 'Mon - Sat | 7:00 am - 5:00 pm', // This would come from establishment settings
            'rating' => 4.6, // This would come from reviews table
            'total_reviews' => 42, // This would come from reviews table
        ];

        // Sample reviews data (in a real app, this would come from database)
        $reviews = [
            [
                'id' => 1,
                'user_name' => 'John Doe',
                'avatar' => null, // Will use initials fallback
                'rating' => 5,
                'comment' => 'Supporting line text lorem ipsum dolor sit amet, consectetur.',
                'date' => '2024-01-15'
            ],
            [
                'id' => 2,
                'user_name' => 'Jane Smith',
                'avatar' => null, // Will use initials fallback
                'rating' => 4,
                'comment' => 'Great quality and fresh bread. Will definitely order again.',
                'date' => '2024-01-12'
            ],
            [
                'id' => 3,
                'user_name' => 'Mike Johnson',
                'avatar' => null, // Will use initials fallback
                'rating' => 5,
                'comment' => 'Excellent value for money. Highly recommended!',
                'date' => '2024-01-10'
            ]
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
        $discountedPrice = (float) $foodItem->discounted_price;
        $discountPercentage = (float) $foodItem->discount_percentage;
        
        // Get establishment name with fallback
        $establishmentName = $foodItem->establishment->business_name ?? 
                           ($foodItem->establishment->owner_fname . ' ' . $foodItem->establishment->owner_lname) ?? 
                           'Unknown Store';
        
        // Get establishment address with fallback
        $establishmentAddress = $foodItem->establishment->address ?? 
                              $foodItem->address ?? 
                              'Location not specified';
        
        return view('consumer.order-confirmation', compact(
            'foodItem', 
            'quantity', 
            'originalPrice', 
            'discountedPrice', 
            'discountPercentage',
            'establishmentName',
            'establishmentAddress',
            'userData'
        ));
    }
}
