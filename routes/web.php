<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FoodListingController;
use App\Http\Controllers\EstablishmentController;
use App\Http\Controllers\ProfileController;

// Public routes
Route::get('/', [HomeController::class, 'index']);
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/about', function () {
    return view('about');
})->name('about');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::get('/registration', [AuthController::class, 'showRegistrationForm'])->name('registration');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/debug-register', function(Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Debug endpoint working! Registration form is submitting data correctly.',
        'redirect' => '/dashboard/consumer',
        'data' => $request->all()
    ]);
});

Route::post('/test-register', function(Request $request) {
    try {
        $request->validate([
            'role' => 'required|in:consumer,establishment,foodbank',
            'email' => 'required|email|unique:consumers,email|unique:establishments,email|unique:foodbanks,email',
            'username' => 'required|unique:consumers,username|unique:establishments,username|unique:foodbanks,username',
            'password' => 'required|min:8|confirmed',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Validation passed!',
            'data' => $request->all()
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    }
});
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected dashboard routes
Route::middleware('custom.auth')->group(function () {
    Route::get('/dashboard/consumer', [DashboardController::class, 'consumer'])->name('dashboard.consumer');
    Route::get('/dashboard/establishment', [DashboardController::class, 'establishment'])->name('dashboard.establishment');
    Route::get('/dashboard/foodbank', [DashboardController::class, 'foodbank'])->name('dashboard.foodbank');
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
    
    // Backward compatibility
    Route::get('/dashboard', [DashboardController::class, 'consumer'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    
    // Food listing routes
    Route::get('/consumer/food-listing', [FoodListingController::class, 'index'])->name('food.listing');
    Route::get('/consumer/food-detail/{id}', [FoodListingController::class, 'show'])->name('food.detail');
    Route::get('/consumer/order-confirmation', [FoodListingController::class, 'orderConfirmation'])->name('order.confirmation');
    Route::get('/consumer/payment-options', [FoodListingController::class, 'paymentOptions'])->name('payment.options');
    Route::get('/consumer/payment', function () {
        return redirect()->route('payment.options');
    });
    Route::get('/consumer/my-orders', [FoodListingController::class, 'myOrders'])->name('my.orders');
    
    // Establishment routes
    Route::prefix('establishment')->name('establishment.')->group(function () {
        Route::get('/dashboard', [EstablishmentController::class, 'dashboard'])->name('dashboard');
        Route::get('/listing-management', [EstablishmentController::class, 'listingManagement'])->name('listing-management');
        Route::get('/order-management', [EstablishmentController::class, 'orderManagement'])->name('order-management');
        Route::get('/announcements', [EstablishmentController::class, 'announcements'])->name('announcements');
        Route::get('/earnings', [EstablishmentController::class, 'earnings'])->name('earnings');
        Route::get('/donation-hub', [EstablishmentController::class, 'donationHub'])->name('donation-hub');
        Route::get('/impact-reports', [EstablishmentController::class, 'impactReports'])->name('impact-reports');
        Route::get('/settings', [EstablishmentController::class, 'settings'])->name('settings');
        Route::get('/help', [EstablishmentController::class, 'help'])->name('help');
        
        // Food listing CRUD routes
        Route::post('/food-listings', [EstablishmentController::class, 'storeFoodListing'])->name('food-listings.store');
        Route::put('/food-listings/{id}', [EstablishmentController::class, 'updateFoodListing'])->name('food-listings.update');
        Route::delete('/food-listings/{id}', [EstablishmentController::class, 'deleteFoodListing'])->name('food-listings.delete');
    });
});
