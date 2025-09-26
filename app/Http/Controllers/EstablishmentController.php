<?php

namespace App\Http\Controllers;

use App\Models\FoodListing;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EstablishmentController extends Controller
{
    public function dashboard()
    {
        // Check if user is logged in and is an establishment
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access the dashboard.');
        }

        $user = $this->getUserData();
        return view('establishment.dashboard', compact('user'));
    }

    /**
     * Get user data from session
     */
    private function getUserData()
    {
        return (object) [
            'id' => Session::get('user_id'),
            'name' => Session::get('user_name', 'User'),
            'fname' => Session::get('fname', ''),
            'lname' => Session::get('lname', ''),
            'email' => Session::get('user_email'),
            'user_type' => Session::get('user_type'),
        ];
    }

    public function listingManagement()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $user = $this->getUserData();
        $establishmentId = Session::get('user_id');
        
        // Get real food listings from database
        $foodItems = FoodListing::where('establishment_id', $establishmentId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'category' => $item->category,
                    'quantity' => (string) $item->quantity,
                    'price' => $item->original_price,
                    'discounted_price' => $item->discounted_price,
                    'discount' => $item->discount_percentage,
                    'expiry' => $item->expiry_date->format('Y-m-d'),
                    'status' => $item->is_expired ? 'expired' : ($item->expiry_date <= now()->addDays(3) ? 'expiring' : 'active'),
                    'image' => $item->image_path ? Storage::url($item->image_path) : 'https://via.placeholder.com/40x40/4a7c59/ffffff?text=' . strtoupper(substr($item->name, 0, 1)),
                    'pickup_available' => $item->pickup_available,
                    'delivery_available' => $item->delivery_available,
                    'address' => $item->address,
                ];
            })
            ->toArray();

        $stats = [
            'total_items' => count($foodItems),
            'active_listings' => count(array_filter($foodItems, fn($item) => $item['status'] === 'active')),
            'expiring_soon' => count(array_filter($foodItems, fn($item) => $item['status'] === 'expiring')),
            'expired_items' => count(array_filter($foodItems, fn($item) => $item['status'] === 'expired')),
            'unsold_items' => count(array_filter($foodItems, fn($item) => $item['status'] === 'active' || $item['status'] === 'expiring'))
        ];

        return view('establishment.listing-management', compact('user', 'foodItems', 'stats'));
    }

    public function orderManagement()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.order-management');
    }

    public function announcements()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.announcements');
    }

    public function earnings()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.earnings');
    }

    public function donationHub()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.donation-hub');
    }

    public function impactReports()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.impact-reports');
    }

    public function settings()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.settings');
    }

    public function help()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.help');
    }

    /**
     * Store a new food listing
     */
    public function storeFoodListing(Request $request)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:fruits-vegetables,baked-goods,cooked-meals,packaged-goods,beverages',
            'quantity' => 'required|integer|min:1',
            'original_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'expiry_date' => 'required|date|after:today',
            'address' => 'nullable|string|max:500',
            'pickup' => 'boolean',
            'delivery' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $establishmentId = Session::get('user_id');
            $data = $request->all();
            
            // Calculate discounted price
            $discountedPrice = null;
            if ($data['discount_percentage'] && $data['discount_percentage'] > 0) {
                $discountAmount = ($data['original_price'] * $data['discount_percentage']) / 100;
                $discountedPrice = $data['original_price'] - $discountAmount;
            }

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('food-listings', 'public');
            }

            $foodListing = FoodListing::create([
                'establishment_id' => $establishmentId,
                'name' => $data['name'],
                'description' => $data['description'],
                'category' => $data['category'],
                'quantity' => $data['quantity'],
                'original_price' => $data['original_price'],
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'discounted_price' => $discountedPrice,
                'expiry_date' => $data['expiry_date'],
                'address' => $data['address'],
                'pickup_available' => $data['pickup'] ?? false,
                'delivery_available' => $data['delivery'] ?? false,
                'image_path' => $imagePath,
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Food listing created successfully',
                'data' => $foodListing
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create food listing'], 500);
        }
    }

    /**
     * Update an existing food listing
     */
    public function updateFoodListing(Request $request, $id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $establishmentId = Session::get('user_id');
        $foodListing = FoodListing::where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->first();

        if (!$foodListing) {
            return response()->json(['error' => 'Food listing not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:fruits-vegetables,baked-goods,cooked-meals,packaged-goods,beverages',
            'quantity' => 'required|integer|min:1',
            'original_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'expiry_date' => 'required|date|after:today',
            'address' => 'nullable|string|max:500',
            'pickup' => 'boolean',
            'delivery' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->all();
            
            // Calculate discounted price
            $discountedPrice = null;
            if ($data['discount_percentage'] && $data['discount_percentage'] > 0) {
                $discountAmount = ($data['original_price'] * $data['discount_percentage']) / 100;
                $discountedPrice = $data['original_price'] - $discountAmount;
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($foodListing->image_path) {
                    Storage::disk('public')->delete($foodListing->image_path);
                }
                $imagePath = $request->file('image')->store('food-listings', 'public');
                $data['image_path'] = $imagePath;
            }

            $foodListing->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'category' => $data['category'],
                'quantity' => $data['quantity'],
                'original_price' => $data['original_price'],
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'discounted_price' => $discountedPrice,
                'expiry_date' => $data['expiry_date'],
                'address' => $data['address'],
                'pickup_available' => $data['pickup'] ?? false,
                'delivery_available' => $data['delivery'] ?? false,
                'image_path' => $data['image_path'] ?? $foodListing->image_path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Food listing updated successfully',
                'data' => $foodListing
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update food listing'], 500);
        }
    }

    /**
     * Delete a food listing
     */
    public function deleteFoodListing($id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $establishmentId = Session::get('user_id');
        $foodListing = FoodListing::where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->first();

        if (!$foodListing) {
            return response()->json(['error' => 'Food listing not found'], 404);
        }

        try {
            // Delete image if exists
            if ($foodListing->image_path) {
                Storage::disk('public')->delete($foodListing->image_path);
            }

            $foodListing->delete();

            return response()->json([
                'success' => true,
                'message' => 'Food listing deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete food listing'], 500);
        }
    }
}
