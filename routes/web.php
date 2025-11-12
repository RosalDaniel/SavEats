<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FoodListingController;
use App\Http\Controllers\EstablishmentController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ============================================================================
// PUBLIC ROUTES
// ============================================================================

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', fn() => view('about'))->name('about');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::get('/registration', [AuthController::class, 'showRegistrationForm'])->name('registration');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// ============================================================================
// PROTECTED ROUTES
// ============================================================================

Route::middleware('custom.auth')->group(function () {
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    
    // Dashboard Routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/consumer', [DashboardController::class, 'consumer'])->name('dashboard.consumer');
        Route::get('/establishment', [DashboardController::class, 'establishment'])->name('dashboard.establishment');
        Route::get('/foodbank', [DashboardController::class, 'foodbank'])->name('dashboard.foodbank');
        Route::get('/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
    });
    
    // Consumer Routes
    Route::prefix('consumer')->name('consumer.')->group(function () {
        Route::get('/food-listing', [FoodListingController::class, 'index'])->name('food-listing');
        Route::get('/food-detail/{id}', [FoodListingController::class, 'show'])->name('food-detail');
        Route::get('/order-confirmation', [FoodListingController::class, 'orderConfirmation'])->name('order-confirmation');
        Route::get('/payment-options', [FoodListingController::class, 'paymentOptions'])->name('payment-options');
        Route::get('/my-orders', [FoodListingController::class, 'myOrders'])->name('my-orders');
        Route::get('/orders/{id}/details', [FoodListingController::class, 'getOrderDetails'])->name('orders.details');
        Route::post('/reviews', [FoodListingController::class, 'submitReview'])->name('reviews.submit');
        Route::get('/orders/{id}/review', [FoodListingController::class, 'getReview'])->name('orders.review');
        Route::get('/my-impact', [FoodListingController::class, 'myImpact'])->name('my-impact');
        Route::get('/announcements', [FoodListingController::class, 'announcements'])->name('announcements');
        Route::get('/help', [FoodListingController::class, 'help'])->name('help');
        Route::get('/settings', [FoodListingController::class, 'settings'])->name('settings');
    });
    
    // Establishment Routes
    Route::prefix('establishment')->name('establishment.')->group(function () {
        Route::get('/dashboard', [EstablishmentController::class, 'dashboard'])->name('dashboard');
        Route::get('/listing-management', [EstablishmentController::class, 'listingManagement'])->name('listing-management');
        Route::get('/order-management', [EstablishmentController::class, 'orderManagement'])->name('order-management');
        Route::get('/earnings', [EstablishmentController::class, 'earnings'])->name('earnings');
        Route::get('/donation-hub', [EstablishmentController::class, 'donationHub'])->name('donation-hub');
        Route::get('/impact-reports', [EstablishmentController::class, 'impactReports'])->name('impact-reports');
        Route::get('/announcements', [EstablishmentController::class, 'announcements'])->name('announcements');
        Route::get('/settings', [EstablishmentController::class, 'settings'])->name('settings');
        Route::get('/help', [EstablishmentController::class, 'help'])->name('help');
        
        // Food Listing CRUD
        Route::post('/food-listings', [EstablishmentController::class, 'storeFoodListing'])->name('food-listings.store');
        Route::put('/food-listings/{id}', [EstablishmentController::class, 'updateFoodListing'])->name('food-listings.update');
        Route::delete('/food-listings/{id}', [EstablishmentController::class, 'deleteFoodListing'])->name('food-listings.delete');
        
        // Order Management
        Route::get('/orders/{id}/details', [EstablishmentController::class, 'getOrderDetails'])->name('orders.details');
        Route::post('/orders/{id}/accept', [EstablishmentController::class, 'acceptOrder'])->name('orders.accept');
        Route::post('/orders/{id}/cancel', [EstablishmentController::class, 'cancelOrder'])->name('orders.cancel');
        Route::post('/orders/{id}/complete', [EstablishmentController::class, 'markOrderComplete'])->name('orders.complete');
    });
    
    // Foodbank Routes
    Route::prefix('foodbank')->name('foodbank.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'foodbank'])->name('dashboard');
        Route::get('/help', [DashboardController::class, 'foodbankHelp'])->name('help');
        Route::get('/settings', [DashboardController::class, 'foodbankSettings'])->name('settings');
    });
    
    // Legacy Routes (for backward compatibility)
    Route::get('/dashboard', [DashboardController::class, 'consumer'])->name('dashboard');
    Route::get('/consumer/food-listing', [FoodListingController::class, 'index'])->name('food.listing');
    Route::get('/consumer/food-detail/{id}', [FoodListingController::class, 'show'])->name('food.detail');
    Route::get('/consumer/order-confirmation', [FoodListingController::class, 'orderConfirmation'])->name('order.confirmation');
    Route::get('/consumer/payment-options', [FoodListingController::class, 'paymentOptions'])->name('payment.options');
    Route::post('/consumer/place-order', [FoodListingController::class, 'placeOrder'])->name('place.order');
    Route::get('/consumer/payment', fn() => redirect()->route('payment.options'));
    Route::get('/consumer/my-orders', [FoodListingController::class, 'myOrders'])->name('my.orders');
    Route::get('/consumer/orders/api', [FoodListingController::class, 'getConsumerOrders'])->name('consumer.orders.api');
    Route::get('/consumer/help', [FoodListingController::class, 'help'])->name('consumer.help');
    Route::get('/consumer/settings', [FoodListingController::class, 'settings'])->name('consumer.settings');
    Route::get('/consumer/my-impact', [FoodListingController::class, 'myImpact'])->name('consumer.my-impact');
    Route::get('/consumer/announcements', [FoodListingController::class, 'announcements'])->name('consumer.announcements');
});
