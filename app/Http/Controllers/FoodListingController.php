<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FoodListing;
use App\Models\Establishment;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Consumer;
use Illuminate\Support\Facades\Storage;

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
        return view('consumer.my-impact', compact('userData'));
    }

    public function announcements()
    {
        $userData = $this->getUserData();
        return view('consumer.announcements', compact('userData'));
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

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $this->calculateDiscountedPrice($item->original_price, $item->discount_percentage),
                    'original_price' => (float) $item->original_price,
                    'discount' => $item->discount_percentage ? round($item->discount_percentage) : 0,
                    'quantity' => (string) $item->quantity,
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
        $orders = Order::with(['foodListing', 'establishment'])
            ->where('consumer_id', $consumerId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Organize orders by status
        $userOrders = [
            'upcoming' => $orders->whereIn('status', ['pending', 'accepted'])->map(function ($order) {
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
                    'status' => $order->status
                ];
            })->values()->toArray(),
            'completed' => $orders->where('status', 'completed')->map(function ($order) {
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
                    'status' => $order->status
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
                    'status' => $order->status
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
            'price' => $this->calculateDiscountedPrice($foodListing->original_price, $foodListing->discount_percentage),
            'original_price' => $foodListing->original_price,
            'discount' => $foodListing->discount_percentage ? round($foodListing->discount_percentage) : 0,
            'quantity' => (string) $foodListing->quantity,
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

    public function paymentOptions(Request $request)
    {
        $userData = $this->getUserData();
        $productId = $request->get('id');
        $quantity = $request->get('quantity', 1);
        $receiveMethod = $request->get('method', 'pickup');
        $phoneNumber = $request->get('phone', '');
        $startTime = $request->get('startTime', '');
        $endTime = $request->get('endTime', '');
        
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
        $deliveryFee = $receiveMethod === 'delivery' ? 57.00 : 0.00;
        $total = $subtotal + $deliveryFee;
        
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
            'deliveryFee',
            'total'
        ));
    }

    /**
     * Place order from payment options page
     */
    public function placeOrder(Request $request)
    {
        $request->validate([
            'food_listing_id' => 'required|exists:food_listings,id',
            'quantity' => 'required|integer|min:1',
            'delivery_method' => 'required|in:pickup,delivery',
            'payment_method' => 'required|in:cash,card,ewallet',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'delivery_address' => 'nullable|string|max:500',
            'pickup_start_time' => 'nullable|date_format:H:i',
            'pickup_end_time' => 'nullable|date_format:H:i',
        ]);

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

            // Calculate prices
            $originalPrice = (float) $foodItem->original_price;
            $discountPercentage = (float) $foodItem->discount_percentage;
            $unitPrice = $this->calculateDiscountedPrice($originalPrice, $discountPercentage);
            $totalPrice = $unitPrice * $request->quantity;

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'consumer_id' => $consumerId,
                'establishment_id' => $foodItem->establishment_id,
                'food_listing_id' => $foodItem->id,
                'quantity' => $request->quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'delivery_method' => $request->delivery_method,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'delivery_address' => $request->delivery_address,
                'pickup_start_time' => $request->pickup_start_time,
                'pickup_end_time' => $request->pickup_end_time,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);

        } catch (\Exception $e) {
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
