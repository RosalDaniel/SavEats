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
| This file contains all the web routes for the SavEats application.
| Routes are organized by access level and user role.
|
| Structure:
| - Public Routes: Accessible to everyone (home, about, login, register)
| - Protected Routes: Require authentication (custom.auth middleware)
| - Role-Based Routes: Require specific user role (role middleware)
|
*/

// ============================================================================
// PUBLIC ROUTES
// ============================================================================

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', fn() => view('about'))->name('about');

// Authentication Routes - Only accessible to guests
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::get('/registration', [AuthController::class, 'showRegistrationForm'])->name('registration');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

// Logout - Accessible to authenticated users
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// ============================================================================
// PROTECTED ROUTES (Require Authentication)
// ============================================================================

Route::middleware('custom.auth')->group(function () {
    
    // ========================================================================
    // PROFILE MANAGEMENT (All authenticated users)
    // ========================================================================
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/request-deletion', [ProfileController::class, 'requestAccountDeletion'])->name('profile.request-deletion');
    
    // ========================================================================
    // CONSUMER ROUTES (Restricted to consumers only)
    // ========================================================================
    Route::prefix('consumer')->name('consumer.')->middleware('role:consumer')->group(function () {
        
        // Dashboard - Standardized path: /consumer/dashboard
        Route::get('/dashboard', [DashboardController::class, 'consumer'])->name('dashboard');
        
        // Food Browsing & Ordering
        Route::get('/food-listing', [FoodListingController::class, 'index'])->name('food-listing');
        Route::get('/food-detail/{id}', [FoodListingController::class, 'show'])->name('food-detail');
        Route::post('/place-order', [FoodListingController::class, 'placeOrder'])->name('place-order');
        Route::get('/order-confirmation', [FoodListingController::class, 'orderConfirmation'])->name('order-confirmation');
        Route::get('/payment-options', [FoodListingController::class, 'paymentOptions'])->name('payment-options');
        
        // Orders Management
        Route::get('/my-orders', [FoodListingController::class, 'myOrders'])->name('my-orders');
        Route::get('/orders/{id}/details', [FoodListingController::class, 'getOrderDetails'])->name('orders.details');
        Route::post('/orders/{id}/cancel', [FoodListingController::class, 'cancelOrder'])->name('orders.cancel');
        Route::get('/orders/api', [FoodListingController::class, 'getConsumerOrders'])->name('orders.api');
        
        // Reviews
        Route::post('/reviews', [FoodListingController::class, 'submitReview'])->name('reviews.submit');
        Route::get('/orders/{id}/review', [FoodListingController::class, 'getReview'])->name('orders.review');
        
        // Other Pages
        Route::get('/my-impact', [FoodListingController::class, 'myImpact'])->name('my-impact');
        Route::get('/announcements', [FoodListingController::class, 'announcements'])->name('announcements');
        Route::get('/help', [FoodListingController::class, 'help'])->name('help');
        Route::get('/settings', [FoodListingController::class, 'settings'])->name('settings');
    });
    
    // ========================================================================
    // ESTABLISHMENT ROUTES (Restricted to establishments only)
    // ========================================================================
    Route::prefix('establishment')->name('establishment.')->middleware('role:establishment')->group(function () {
        
        // Dashboard - Standardized path: /establishment/dashboard
        Route::get('/dashboard', [EstablishmentController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/ratings', [EstablishmentController::class, 'getRatings'])->name('dashboard.ratings');
        
        // Food Listing Management
        Route::get('/listing-management', [EstablishmentController::class, 'listingManagement'])->name('listing-management');
        Route::post('/food-listings', [EstablishmentController::class, 'storeFoodListing'])->name('food-listings.store');
        Route::put('/food-listings/{id}', [EstablishmentController::class, 'updateFoodListing'])->name('food-listings.update');
        Route::delete('/food-listings/{id}', [EstablishmentController::class, 'deleteFoodListing'])->name('food-listings.delete');
        
        // Order Management
        Route::get('/order-management', [EstablishmentController::class, 'orderManagement'])->name('order-management');
        Route::get('/orders/{id}/details', [EstablishmentController::class, 'getOrderDetails'])->name('orders.details');
        Route::post('/orders/{id}/accept', [EstablishmentController::class, 'acceptOrder'])->name('orders.accept');
        Route::post('/orders/{id}/cancel', [EstablishmentController::class, 'cancelOrder'])->name('orders.cancel');
        Route::post('/orders/{id}/complete', [EstablishmentController::class, 'markOrderComplete'])->name('orders.complete');
        
        // Financial & Reports
        Route::get('/earnings', [EstablishmentController::class, 'earnings'])->name('earnings');
        Route::get('/impact-reports', [EstablishmentController::class, 'impactReports'])->name('impact-reports');
        Route::get('/donation-hub', [EstablishmentController::class, 'donationHub'])->name('donation-hub');
        Route::post('/donation-request', [EstablishmentController::class, 'storeDonationRequest'])->name('donation-request.store');
        Route::get('/donation-history', [EstablishmentController::class, 'donationHistory'])->name('donation-history');
        Route::get('/donation-history/export/{type}', [EstablishmentController::class, 'exportDonationHistory'])->name('donation-history.export');
        
        // Other Pages
        Route::get('/announcements', [EstablishmentController::class, 'announcements'])->name('announcements');
        Route::get('/settings', [EstablishmentController::class, 'settings'])->name('settings');
        Route::get('/help', [EstablishmentController::class, 'help'])->name('help');
    });
    
    // ========================================================================
    // FOODBANK ROUTES (Restricted to foodbanks only)
    // ========================================================================
    Route::prefix('foodbank')->name('foodbank.')->middleware('role:foodbank')->group(function () {
        
        // Dashboard - Standardized path: /foodbank/dashboard
        Route::get('/dashboard', [DashboardController::class, 'foodbank'])->name('dashboard');
        
        // Donation Request
        Route::get('/donation-request', [DashboardController::class, 'donationRequest'])->name('donation-request');
        Route::post('/donation-request', [DashboardController::class, 'storeDonationRequest'])->name('donation-request.store');
        Route::post('/donation-request/accept/{donationId}', [DashboardController::class, 'acceptDonation'])->name('donation-request.accept');
        Route::post('/donation-request/decline/{donationId}', [DashboardController::class, 'declineDonation'])->name('donation-request.decline');
        
        // Partner Network
        Route::get('/partner-network', [DashboardController::class, 'partnerNetwork'])->name('partner-network');
        
        // Donation History
        Route::get('/donation-history', [DashboardController::class, 'donationHistory'])->name('donation-history');
        Route::get('/donation-history/export', [DashboardController::class, 'exportDonationHistory'])->name('donation-history.export');
        
        // Impact Reports
        Route::get('/impact-reports', [DashboardController::class, 'foodbankImpactReports'])->name('impact-reports');
        
        // Other Pages
        Route::get('/announcements', [DashboardController::class, 'foodbankAnnouncements'])->name('announcements');
        Route::get('/help', [DashboardController::class, 'foodbankHelp'])->name('help');
        Route::get('/settings', [DashboardController::class, 'foodbankSettings'])->name('settings');
    });
    
    // ========================================================================
    // ADMIN ROUTES (Restricted to admins only)
    // ========================================================================
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        
        // Dashboard - Standardized path: /admin/dashboard
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        
        // User Management
        Route::get('/users', [DashboardController::class, 'adminUsers'])->name('users');
        Route::post('/users/{role}/{id}/status', [DashboardController::class, 'updateUserStatus'])->name('users.updateStatus');
        Route::post('/users/{role}/{id}/info', [DashboardController::class, 'updateUserInfo'])->name('users.updateInfo');
        Route::delete('/users/{role}/{id}', [DashboardController::class, 'deleteUser'])->name('users.delete');
        
        // Establishments Management
        Route::get('/establishments', [DashboardController::class, 'adminEstablishments'])->name('establishments');
        Route::post('/establishments/{id}/status', [DashboardController::class, 'updateEstablishmentStatus'])->name('establishments.updateStatus');
        Route::post('/establishments/{id}/verification', [DashboardController::class, 'toggleEstablishmentVerification'])->name('establishments.toggleVerification');
        Route::post('/establishments/{id}/violation', [DashboardController::class, 'addEstablishmentViolation'])->name('establishments.addViolation');
        Route::delete('/establishments/{id}', [DashboardController::class, 'deleteEstablishment'])->name('establishments.delete');
        
        // Food Listings Management
        Route::get('/food-listings', [DashboardController::class, 'adminFoodListings'])->name('food-listings');
        Route::post('/food-listings/{id}/status', [DashboardController::class, 'updateFoodListingStatus'])->name('food-listings.updateStatus');
        Route::delete('/food-listings/{id}', [DashboardController::class, 'deleteFoodListing'])->name('food-listings.delete');
        
        // Order Management
        Route::get('/orders', [DashboardController::class, 'adminOrders'])->name('orders');
        Route::post('/orders/{id}/force-cancel', [DashboardController::class, 'forceCancelOrder'])->name('orders.forceCancel');
        Route::post('/orders/{id}/resolve-dispute', [DashboardController::class, 'resolveDispute'])->name('orders.resolveDispute');
        
        // Donation Hub
        Route::get('/donations', [DashboardController::class, 'adminDonations'])->name('donations');
        Route::get('/donations/export/csv', [DashboardController::class, 'exportDonationsToCsv'])->name('donations.exportCsv');
        
        // Food Banks Management
        Route::get('/foodbanks', [DashboardController::class, 'adminFoodbanks'])->name('foodbanks');
        
        // Reports & Analytics
        Route::get('/reports', [DashboardController::class, 'adminReports'])->name('reports');
        
        // Announcements
        Route::get('/announcements', [DashboardController::class, 'adminAnnouncements'])->name('announcements');
        Route::post('/announcements', [DashboardController::class, 'storeAnnouncement'])->name('announcements.store');
        Route::post('/announcements/{id}', [DashboardController::class, 'updateAnnouncement'])->name('announcements.update');
        Route::delete('/announcements/{id}', [DashboardController::class, 'deleteAnnouncement'])->name('announcements.delete');
        
        // Review Management
        Route::get('/reviews', [DashboardController::class, 'adminReviews'])->name('reviews');
        Route::post('/reviews/{id}/flag', [DashboardController::class, 'flagReview'])->name('reviews.flag');
        Route::delete('/reviews/{id}', [DashboardController::class, 'deleteReview'])->name('reviews.delete');
        
        // System Logs
        Route::get('/system-logs', [\App\Http\Controllers\SystemLogController::class, 'index'])->name('system-logs');
        Route::get('/system-logs/data', [\App\Http\Controllers\SystemLogController::class, 'getLogs'])->name('system-logs.data');
        Route::get('/system-logs/export/csv', [\App\Http\Controllers\SystemLogController::class, 'exportCsv'])->name('system-logs.export.csv');
        Route::get('/system-logs/export/pdf', [\App\Http\Controllers\SystemLogController::class, 'exportPdf'])->name('system-logs.export.pdf');
        Route::get('/system-logs/export/excel', [\App\Http\Controllers\SystemLogController::class, 'exportExcel'])->name('system-logs.export.excel');
        Route::delete('/system-logs/cleanup', [\App\Http\Controllers\SystemLogController::class, 'deleteOldLogs'])->name('system-logs.cleanup');
        
        // Content Management System
        Route::get('/cms', [\App\Http\Controllers\AdminCmsController::class, 'index'])->name('cms');
        
        // Banners
        Route::get('/cms/banners', [\App\Http\Controllers\AdminCmsController::class, 'getBanners'])->name('cms.banners');
        Route::post('/cms/banners', [\App\Http\Controllers\AdminCmsController::class, 'storeBanner'])->name('cms.banners.store');
        Route::post('/cms/banners/{id}', [\App\Http\Controllers\AdminCmsController::class, 'updateBanner'])->name('cms.banners.update');
        Route::delete('/cms/banners/{id}', [\App\Http\Controllers\AdminCmsController::class, 'deleteBanner'])->name('cms.banners.delete');
        
        // Help Articles
        Route::get('/cms/articles', [\App\Http\Controllers\AdminCmsController::class, 'getArticles'])->name('cms.articles');
        Route::post('/cms/articles', [\App\Http\Controllers\AdminCmsController::class, 'storeArticle'])->name('cms.articles.store');
        Route::post('/cms/articles/{id}', [\App\Http\Controllers\AdminCmsController::class, 'updateArticle'])->name('cms.articles.update');
        Route::delete('/cms/articles/{id}', [\App\Http\Controllers\AdminCmsController::class, 'deleteArticle'])->name('cms.articles.delete');
        
        // Terms & Conditions
        Route::get('/cms/terms', [\App\Http\Controllers\AdminCmsController::class, 'getTerms'])->name('cms.terms');
        Route::post('/cms/terms', [\App\Http\Controllers\AdminCmsController::class, 'storeTerms'])->name('cms.terms.store');
        Route::post('/cms/terms/{id}', [\App\Http\Controllers\AdminCmsController::class, 'updateTerms'])->name('cms.terms.update');
        Route::delete('/cms/terms/{id}', [\App\Http\Controllers\AdminCmsController::class, 'deleteTerms'])->name('cms.terms.delete');
        
        // Privacy Policy
        Route::get('/cms/privacy', [\App\Http\Controllers\AdminCmsController::class, 'getPrivacy'])->name('cms.privacy');
        Route::post('/cms/privacy', [\App\Http\Controllers\AdminCmsController::class, 'storePrivacy'])->name('cms.privacy.store');
        Route::post('/cms/privacy/{id}', [\App\Http\Controllers\AdminCmsController::class, 'updatePrivacy'])->name('cms.privacy.update');
        Route::delete('/cms/privacy/{id}', [\App\Http\Controllers\AdminCmsController::class, 'deletePrivacy'])->name('cms.privacy.delete');
        
        // System Settings
        Route::get('/settings', [DashboardController::class, 'adminSettings'])->name('settings');
    });
    
    // ========================================================================
    // LEGACY ROUTE ALIASES (Backward Compatibility)
    // ========================================================================
    // These routes maintain backward compatibility with old route names/paths
    // They point to the same controllers but use legacy route names for compatibility
    
    // Legacy Dashboard Routes - Point to same controllers, preserve old route names
    Route::get('/dashboard', [DashboardController::class, 'consumer'])
        ->middleware('role:consumer')
        ->name('dashboard');
    
    Route::get('/dashboard/consumer', [DashboardController::class, 'consumer'])
        ->middleware('role:consumer')
        ->name('dashboard.consumer');
    
    Route::get('/dashboard/establishment', [DashboardController::class, 'establishment'])
        ->middleware('role:establishment')
        ->name('dashboard.establishment');
    
    Route::get('/dashboard/foodbank', [DashboardController::class, 'foodbank'])
        ->middleware('role:foodbank')
        ->name('dashboard.foodbank');
    
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('dashboard.admin');
    
    // Legacy Consumer Routes - Point to same controllers, preserve old route names
    Route::get('/consumer/food-listing', [FoodListingController::class, 'index'])
        ->middleware('role:consumer')
        ->name('food.listing');
    
    Route::get('/consumer/food-detail/{id}', [FoodListingController::class, 'show'])
        ->middleware('role:consumer')
        ->name('food.detail');
    
    Route::get('/consumer/order-confirmation', [FoodListingController::class, 'orderConfirmation'])
        ->middleware('role:consumer')
        ->name('order.confirmation');
    
    Route::get('/consumer/payment-options', [FoodListingController::class, 'paymentOptions'])
        ->middleware('role:consumer')
        ->name('payment.options');
    
    Route::post('/consumer/place-order', [FoodListingController::class, 'placeOrder'])
        ->middleware('role:consumer')
        ->name('place.order');
    
    Route::get('/consumer/payment', fn() => redirect()->route('payment.options'))
        ->middleware('role:consumer');
    
    Route::get('/consumer/my-orders', [FoodListingController::class, 'myOrders'])
        ->middleware('role:consumer')
        ->name('my.orders');
    
    Route::get('/consumer/orders/api', [FoodListingController::class, 'getConsumerOrders'])
        ->middleware('role:consumer')
        ->name('consumer.orders.api');
    
    Route::get('/consumer/help', [FoodListingController::class, 'help'])
        ->middleware('role:consumer')
        ->name('consumer.help');
    
    Route::get('/consumer/settings', [FoodListingController::class, 'settings'])
        ->middleware('role:consumer')
        ->name('consumer.settings');
    
    Route::get('/consumer/my-impact', [FoodListingController::class, 'myImpact'])
        ->middleware('role:consumer')
        ->name('consumer.my-impact');
    
    Route::get('/consumer/announcements', [FoodListingController::class, 'announcements'])
        ->middleware('role:consumer')
        ->name('consumer.announcements');
});
