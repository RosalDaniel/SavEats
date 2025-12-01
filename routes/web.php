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

// Terms & Conditions and Privacy Policy - Public access
Route::get('/terms', [\App\Http\Controllers\CmsController::class, 'termsPage'])->name('terms');
Route::get('/privacy', [\App\Http\Controllers\CmsController::class, 'privacyPage'])->name('privacy');

// Authentication Routes - Only accessible to guests
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::get('/registration', [AuthController::class, 'showRegistrationForm'])->name('registration');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    
    // Password Recovery Routes (Admin blocked via middleware)
    Route::middleware([\App\Http\Middleware\BlockAdminFromRecovery::class])->group(function () {
        Route::get('/forgot-password', [\App\Http\Controllers\PasswordRecoveryController::class, 'showForgotPassword'])->name('password-recovery.forgot');
        Route::post('/forgot-password', [\App\Http\Controllers\PasswordRecoveryController::class, 'requestReset'])->name('password-recovery.request');
        Route::get('/reset-password/{token}', [\App\Http\Controllers\PasswordRecoveryController::class, 'showResetPassword'])->name('password-recovery.reset');
        Route::post('/reset-password', [\App\Http\Controllers\PasswordRecoveryController::class, 'resetPassword'])->name('password-recovery.reset.submit');
        Route::get('/verify-email/{token}', [\App\Http\Controllers\PasswordRecoveryController::class, 'verifyEmail'])->name('password-recovery.verify-email');
        Route::get('/resend-verification', function() {
            return view('auth.resend-verification');
        })->name('password-recovery.resend-verification.show');
        Route::post('/resend-verification', [\App\Http\Controllers\PasswordRecoveryController::class, 'resendVerification'])->name('password-recovery.resend-verification');
    });
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
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
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
        Route::get('/food-listings/{id}/reviews', [EstablishmentController::class, 'getFoodListingReviews'])->name('food-listings.reviews');
        
        // Order Management
        Route::get('/order-management', [EstablishmentController::class, 'orderManagement'])->name('order-management');
        Route::get('/orders/{id}/details', [EstablishmentController::class, 'getOrderDetails'])->name('orders.details');
        Route::post('/orders/{id}/accept', [EstablishmentController::class, 'acceptOrder'])->name('orders.accept');
        Route::post('/orders/{id}/cancel', [EstablishmentController::class, 'cancelOrder'])->name('orders.cancel');
        Route::post('/orders/{id}/complete', [EstablishmentController::class, 'markOrderComplete'])->name('orders.complete');
        
        // Financial & Reports
        Route::get('/earnings', [EstablishmentController::class, 'earnings'])->name('earnings');
        Route::get('/impact-reports', [EstablishmentController::class, 'impactReports'])->name('impact-reports');
        Route::post('/impact-reports/export/{format}', [EstablishmentController::class, 'exportImpactReports'])->name('impact-reports.export');
        Route::get('/donation-hub', [EstablishmentController::class, 'donationHub'])->name('donation-hub');
        Route::get('/my-donation-requests', [EstablishmentController::class, 'myDonationRequests'])->name('my-donation-requests');
        Route::post('/donation-request', [EstablishmentController::class, 'storeDonationRequest'])->name('donation-request.store');
        Route::post('/donation-request/fulfill/{requestId}', [EstablishmentController::class, 'fulfillDonationRequest'])->name('donation-request.fulfill');
        Route::get('/foodbank/contact/{foodbankId}', [EstablishmentController::class, 'getFoodbankContact'])->name('foodbank.contact');
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
        Route::get('/donation-requests-list', [DashboardController::class, 'donationRequestsList'])->name('donation-requests-list');
        Route::get('/donation-request/{id}', [DashboardController::class, 'showDonationRequest'])->name('donation-request.show');
        Route::post('/donation-request', [DashboardController::class, 'storeDonationRequest'])->name('donation-request.store');
        Route::put('/donation-request/{id}', [DashboardController::class, 'updateDonationRequest'])->name('donation-request.update');
        Route::delete('/donation-request/{id}', [DashboardController::class, 'deleteDonationRequest'])->name('donation-request.delete');
        Route::post('/donation-request/accept/{donationId}', [DashboardController::class, 'acceptDonation'])->name('donation-request.accept');
        Route::post('/donation-request/decline/{donationId}', [DashboardController::class, 'declineDonation'])->name('donation-request.decline');
        // New routes for donation requests from establishments
        Route::post('/donation-request/accept-request/{requestId}', [DashboardController::class, 'acceptDonationRequest'])->name('donation-request.accept-request');
        Route::post('/donation-request/decline-request/{requestId}', [DashboardController::class, 'declineDonationRequest'])->name('donation-request.decline-request');
        Route::post('/donation-request/confirm-pickup/{requestId}', [DashboardController::class, 'confirmPickup'])->name('donation-request.confirm-pickup');
        Route::post('/donation-request/confirm-delivery/{requestId}', [DashboardController::class, 'confirmDelivery'])->name('donation-request.confirm-delivery');
        Route::post('/donation-request/confirm-foodbank-pickup/{requestId}', [DashboardController::class, 'confirmFoodbankRequestPickup'])->name('donation-request.confirm-foodbank-pickup');
        Route::post('/donation-request/confirm-foodbank-delivery/{requestId}', [DashboardController::class, 'confirmFoodbankRequestDelivery'])->name('donation-request.confirm-foodbank-delivery');
        Route::get('/establishment/contact/{establishmentId}', [DashboardController::class, 'getEstablishmentContact'])->name('establishment.contact');
        
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

        // Food Bank Management
        Route::get('/foodbanks', [DashboardController::class, 'adminFoodbanks'])->name('foodbanks');
        Route::get('/foodbanks/{id}', [DashboardController::class, 'viewFoodbankDetails'])->name('foodbanks.details');
        Route::post('/foodbanks/{id}/status', [DashboardController::class, 'updateFoodbankStatus'])->name('foodbanks.updateStatus');
        Route::post('/foodbanks/{id}/verification', [DashboardController::class, 'toggleFoodbankVerification'])->name('foodbanks.toggleVerification');
        Route::delete('/foodbanks/{id}', [DashboardController::class, 'deleteFoodbank'])->name('foodbanks.delete');
        Route::get('/foodbank-donation-hub', [DashboardController::class, 'adminFoodbankDonationHub'])->name('foodbank-donation-hub');
        
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
        
        // Announcements (moved to CMS)
        Route::get('/cms/announcements', [\App\Http\Controllers\AdminCmsController::class, 'getAnnouncements'])->name('cms.announcements');
        Route::post('/cms/announcements', [\App\Http\Controllers\AdminCmsController::class, 'storeAnnouncement'])->name('cms.announcements.store');
        Route::post('/cms/announcements/{id}', [\App\Http\Controllers\AdminCmsController::class, 'updateAnnouncement'])->name('cms.announcements.update');
        Route::delete('/cms/announcements/{id}', [\App\Http\Controllers\AdminCmsController::class, 'deleteAnnouncement'])->name('cms.announcements.delete');
        
        // Review Management
        Route::get('/reviews', [DashboardController::class, 'adminReviews'])->name('reviews');
        Route::post('/reviews/{id}/flag', [DashboardController::class, 'flagReview'])->name('reviews.flag');
        Route::delete('/reviews/{id}', [DashboardController::class, 'deleteReview'])->name('reviews.delete');
        
        // System Logs
        Route::get('/system-logs', [\App\Http\Controllers\SystemLogController::class, 'index'])->name('system-logs');
        Route::get('/system-logs/data', [\App\Http\Controllers\SystemLogController::class, 'getLogs'])->name('system-logs.data');
        Route::get('/system-logs/donation-activity', [\App\Http\Controllers\SystemLogController::class, 'getDonationActivity'])->name('system-logs.donation-activity');
        Route::get('/system-logs/export/csv', [\App\Http\Controllers\SystemLogController::class, 'exportCsv'])->name('system-logs.export.csv');
        Route::get('/system-logs/export/pdf', [\App\Http\Controllers\SystemLogController::class, 'exportPdf'])->name('system-logs.export.pdf');
        Route::get('/system-logs/export/excel', [\App\Http\Controllers\SystemLogController::class, 'exportExcel'])->name('system-logs.export.excel');
        Route::delete('/system-logs/cleanup', [\App\Http\Controllers\SystemLogController::class, 'deleteOldLogs'])->name('system-logs.cleanup');
        
        // Content Management System
        Route::get('/cms', [\App\Http\Controllers\AdminCmsController::class, 'index'])->name('cms');
        
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
        Route::get('/settings', [\App\Http\Controllers\SystemSettingsController::class, 'index'])->name('settings');
        Route::post('/settings/update', [\App\Http\Controllers\SystemSettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/reset', [\App\Http\Controllers\SystemSettingsController::class, 'reset'])->name('settings.reset');
        
        // Admin Notifications
        Route::get('/notifications', [\App\Http\Controllers\AdminNotificationController::class, 'viewAll'])->name('notifications.view-all');
        Route::get('/api/notifications', [\App\Http\Controllers\AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::get('/api/notifications/unread-count', [\App\Http\Controllers\AdminNotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::get('/api/notifications/{id}', [\App\Http\Controllers\AdminNotificationController::class, 'show'])->name('notifications.show');
        Route::post('/api/notifications/{id}/read', [\App\Http\Controllers\AdminNotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/api/notifications/{id}/unread', [\App\Http\Controllers\AdminNotificationController::class, 'markAsUnread'])->name('notifications.mark-unread');
        Route::post('/api/notifications/mark-all-read', [\App\Http\Controllers\AdminNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::delete('/api/notifications/{id}', [\App\Http\Controllers\AdminNotificationController::class, 'destroy'])->name('notifications.delete');
    });
    
    // ========================================================================
    // CMS FRONTEND ROUTES (Accessible to all authenticated users)
    // ========================================================================
    // Help Articles API
    Route::get('/cms/articles', [\App\Http\Controllers\CmsController::class, 'getArticles'])->name('cms.articles.public');
    Route::get('/cms/articles/{identifier}', [\App\Http\Controllers\CmsController::class, 'getArticle'])->name('cms.article.show');
    Route::get('/cms/categories', [\App\Http\Controllers\CmsController::class, 'getCategories'])->name('cms.categories');
    
    // Terms & Conditions API
    Route::get('/cms/terms', [\App\Http\Controllers\CmsController::class, 'getTerms'])->name('cms.terms.public');
    
    // Privacy Policy API
    Route::get('/cms/privacy', [\App\Http\Controllers\CmsController::class, 'getPrivacy'])->name('cms.privacy.public');
    
    // ========================================================================
    // NOTIFICATION ROUTES (Accessible to all authenticated users)
    // ========================================================================
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.delete');
    
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
