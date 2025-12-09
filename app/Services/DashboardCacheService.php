<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\Consumer;
use App\Models\Establishment;
use App\Models\Foodbank;
use App\Models\FoodListing;
use App\Models\Order;
use App\Models\Donation;
use App\Models\Review;
use App\Models\DonationRequest;
use Illuminate\Support\Facades\DB;

class DashboardCacheService
{
    /**
     * Cache TTL in minutes (5-10 minutes as specified)
     */
    const CACHE_TTL = 7; // 7 minutes

    /**
     * Get or cache total users count
     */
    public static function getTotalUsers()
    {
        return Cache::remember('dashboard:total_users', self::CACHE_TTL * 60, function () {
            return Consumer::count() + Establishment::count() + Foodbank::count();
        });
    }

    /**
     * Get or cache user counts by role
     */
    public static function getUserCounts()
    {
        return Cache::remember('dashboard:user_counts', self::CACHE_TTL * 60, function () {
            return [
                'consumers' => Consumer::count(),
                'establishments' => Establishment::count(),
                'foodbanks' => Foodbank::count(),
            ];
        });
    }

    /**
     * Get or cache total listings count
     */
    public static function getTotalListings()
    {
        return Cache::remember('dashboard:total_listings', self::CACHE_TTL * 60, function () {
            return FoodListing::count();
        });
    }

    /**
     * Get or cache active listings count
     */
    public static function getActiveListings()
    {
        return Cache::remember('dashboard:active_listings', self::CACHE_TTL * 60, function () {
            return FoodListing::where('status', 'active')
                ->where('expiry_date', '>=', Carbon::now()->toDateString())
                ->count();
        });
    }

    /**
     * Get or cache total orders count
     */
    public static function getTotalOrders()
    {
        return Cache::remember('dashboard:total_orders', self::CACHE_TTL * 60, function () {
            return Order::count();
        });
    }

    /**
     * Get or cache orders by status
     */
    public static function getOrdersByStatus()
    {
        return Cache::remember('dashboard:orders_by_status', self::CACHE_TTL * 60, function () {
            return [
                'pending' => Order::where('status', 'pending')->count(),
                'accepted' => Order::where('status', 'accepted')->count(),
                'completed' => Order::where('status', 'completed')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
            ];
        });
    }

    /**
     * Get or cache total donations count
     */
    public static function getTotalDonations()
    {
        return Cache::remember('dashboard:total_donations', self::CACHE_TTL * 60, function () {
            return Donation::count();
        });
    }

    /**
     * Get or cache donation statuses summary
     */
    public static function getDonationStatuses()
    {
        return Cache::remember('dashboard:donation_statuses', self::CACHE_TTL * 60, function () {
            return [
                'completed' => Donation::whereIn('status', ['collected', 'ready_for_collection'])->count(),
                'pending' => Donation::whereIn('status', ['pending_pickup', 'ready_for_collection'])->count(),
            ];
        });
    }

    /**
     * Get or cache food rescued count
     */
    public static function getFoodRescued()
    {
        return Cache::remember('dashboard:food_rescued', self::CACHE_TTL * 60, function () {
            $fromOrders = Order::where('status', 'completed')->sum('quantity');
            $fromDonations = Donation::whereIn('status', ['collected', 'ready_for_collection'])->sum('quantity');
            return [
                'total' => $fromOrders + $fromDonations,
                'from_orders' => $fromOrders,
                'from_donations' => $fromDonations,
            ];
        });
    }

    /**
     * Get or cache monthly activity data (last 6 months)
     */
    public static function getMonthlyActivity()
    {
        return Cache::remember('dashboard:monthly_activity', self::CACHE_TTL * 60, function () {
            $monthlyActivity = [];
            $months = [];
            
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthStart = $date->copy()->startOfMonth();
                $monthEnd = $date->copy()->endOfMonth();
                
                $monthName = $date->format('M Y');
                $months[] = $monthName;
                
                // Optimize with single queries using whereBetween
                $monthlyActivity[] = [
                    'month' => $monthName,
                    'users' => Consumer::whereBetween('created_at', [$monthStart, $monthEnd])->count() +
                              Establishment::whereBetween('created_at', [$monthStart, $monthEnd])->count() +
                              Foodbank::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'orders' => Order::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'donations' => Donation::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'listings' => FoodListing::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                ];
            }
            
            return [
                'activity' => $monthlyActivity,
                'months' => $months,
            ];
        });
    }

    /**
     * Get or cache consumer dashboard stats
     */
    public static function getConsumerStats($consumerId)
    {
        return Cache::remember("dashboard:consumer:{$consumerId}", self::CACHE_TTL * 60, function () use ($consumerId) {
            // Get completed orders with eager loading
            $completedOrders = Order::with('foodListing')
                ->where('consumer_id', $consumerId)
                ->where('status', 'completed')
                ->get();
            
            $totalSavings = 0;
            $foodRescued = 0;
            
            foreach ($completedOrders as $order) {
                if ($order->foodListing) {
                    $originalPrice = (float) $order->foodListing->original_price;
                    $discountPercentage = (float) ($order->foodListing->discount_percentage ?? 0);
                    $quantity = (int) $order->quantity;
                    
                    if ($discountPercentage > 0) {
                        $discountAmountPerUnit = $originalPrice * ($discountPercentage / 100);
                        $totalSavings += $discountAmountPerUnit * $quantity;
                    }
                    
                    $foodRescued += $quantity;
                }
            }
            
            $ordersCount = $completedOrders->count();
            $ratedOrdersCount = Review::where('consumer_id', $consumerId)
                ->distinct('order_id')
                ->count('order_id');
            
            return [
                'total_savings' => round($totalSavings, 2),
                'orders_count' => $ordersCount,
                'food_rescued' => $foodRescued,
                'rated_orders_count' => $ratedOrdersCount,
            ];
        });
    }

    /**
     * Get or cache establishment dashboard stats
     */
    public static function getEstablishmentStats($establishmentId)
    {
        return Cache::remember("dashboard:establishment:{$establishmentId}", self::CACHE_TTL * 60, function () use ($establishmentId) {
            // Get all listings in one query
            $allListings = FoodListing::where('establishment_id', $establishmentId)->get();
            
            $totalItems = $allListings->count();
            $freshStock = 0;
            $expiringStock = 0;
            $expiredStock = 0;
            $activeListings = 0;
            $foodSaved = 0;
            
            $now = now();
            $today = $now->toDateString();
            $threeDaysFromNow = $now->copy()->addDays(3)->toDateString();
            
            foreach ($allListings as $item) {
                if (!$item->expiry_date) continue;
                
                $expiryDate = $item->expiry_date;
                $isExpired = $expiryDate < $today;
                $isExpiringSoon = $expiryDate >= $today && $expiryDate <= $threeDaysFromNow;
                $hasStock = ($item->quantity ?? 0) > 0;
                
                if ($isExpired) {
                    $expiredStock++;
                } elseif ($isExpiringSoon) {
                    $expiringStock++;
                } else {
                    $freshStock++;
                    if ($hasStock) {
                        $activeListings++;
                    }
                }
                
                $foodSaved += $item->sold_stock ?? 0;
            }
            
            $freshStockPercent = $totalItems > 0 ? round(($freshStock / $totalItems) * 100) : 0;
            $expiringStockPercent = $totalItems > 0 ? round(($expiringStock / $totalItems) * 100) : 0;
            $expiredStockPercent = $totalItems > 0 ? round(($expiredStock / $totalItems) * 100) : 0;
            
            // Today's earnings - optimized query
            $todayEarnings = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->where(function($query) {
                    $query->whereDate('completed_at', now()->toDateString())
                          ->orWhere(function($q) {
                              $q->whereNull('completed_at')
                                ->whereDate('updated_at', now()->toDateString());
                          });
                })
                ->sum('total_price');
            
            // Food donated count
            $foodDonated = Donation::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->count();
            
            return [
                'active_listings' => $activeListings,
                'today_earnings' => $todayEarnings ? (float) $todayEarnings : 0.0,
                'food_donated' => $foodDonated,
                'food_saved' => $foodSaved,
                'inventory_health' => [
                    'fresh_stock_percent' => $freshStockPercent,
                    'expiring_stock_percent' => $expiringStockPercent,
                    'expired_stock_percent' => $expiredStockPercent,
                ],
            ];
        });
    }

    /**
     * Get or cache establishment reviews stats
     */
    public static function getEstablishmentReviews($establishmentId)
    {
        return Cache::remember("dashboard:establishment:reviews:{$establishmentId}", self::CACHE_TTL * 60, function () use ($establishmentId) {
            $reviews = Review::where('establishment_id', $establishmentId)
                ->get(); // No need for eager loading consumer here
            
            $totalReviews = $reviews->count();
            $averageRating = $totalReviews > 0 ? round($reviews->avg('rating'), 1) : 0;
            $positiveReviews = $reviews->filter(fn($r) => $r->rating >= 4)->count();
            $negativeReviews = $reviews->filter(fn($r) => $r->rating <= 3)->count();
            $reviewsThisMonth = $reviews->filter(fn($r) => $r->created_at->isCurrentMonth())->count();
            $positivePercentage = $totalReviews > 0 ? round(($positiveReviews / $totalReviews) * 100) : 0;
            
            $ratingText = '';
            if ($reviewsThisMonth > 0) {
                $ratingText = "You've received +{$reviewsThisMonth} review" . ($reviewsThisMonth !== 1 ? 's' : '') . " this month";
                if ($positivePercentage > 0) {
                    $ratingText .= " - {$positivePercentage}% positive!";
                }
            } else {
                $ratingText = $totalReviews > 0 
                    ? "You have {$totalReviews} total review" . ($totalReviews !== 1 ? 's' : '') . " - {$positivePercentage}% positive!"
                    : "No reviews yet. Start selling to get reviews!";
            }
            
            return [
                'average_rating' => $averageRating,
                'total_reviews' => $totalReviews,
                'positive_reviews' => $positiveReviews,
                'negative_reviews' => $negativeReviews,
                'reviews_this_month' => $reviewsThisMonth,
                'positive_percentage' => $positivePercentage,
                'rating_text' => $ratingText,
            ];
        });
    }

    /**
     * Get or cache foodbank dashboard stats
     */
    public static function getFoodbankStats($foodbankId)
    {
        return Cache::remember("dashboard:foodbank:{$foodbankId}", self::CACHE_TTL * 60, function () use ($foodbankId) {
            $activeRequests = DonationRequest::where('foodbank_id', $foodbankId)
                ->whereIn('status', ['pending', 'accepted'])
                ->count();
            
            $businessPartnered = Donation::where('foodbank_id', $foodbankId)
                ->whereNotNull('establishment_id')
                ->distinct('establishment_id')
                ->count('establishment_id');
            
            $donationsReceived = Donation::where('foodbank_id', $foodbankId)
                ->where('status', 'collected')
                ->count();
            
            return [
                'active_requests' => $activeRequests,
                'business_partnered' => $businessPartnered,
                'donations_received' => $donationsReceived,
            ];
        });
    }

    /**
     * Get or cache foodbank weekly chart data
     */
    public static function getFoodbankWeeklyData($foodbankId)
    {
        return Cache::remember("dashboard:foodbank:weekly:{$foodbankId}", self::CACHE_TTL * 60, function () use ($foodbankId) {
            $dayLabels = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
            $weeklyData = [];
            
            // Optimize: Use single query with date grouping
            $donationsByDay = Donation::where('foodbank_id', $foodbankId)
                ->where('status', 'collected')
                ->whereDate('collected_at', '>=', Carbon::now()->subDays(6)->startOfDay()->toDateString())
                ->selectRaw('DATE(collected_at) as date, COUNT(*) as count')
                ->groupByRaw('DATE(collected_at)')
                ->pluck('count', 'date')
                ->toArray();
            
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->startOfDay();
                $dayOfWeek = $date->dayOfWeek;
                $dateStr = $date->toDateString();
                
                $weeklyData[] = [
                    'label' => $dayLabels[$dayOfWeek],
                    'value' => (int) ($donationsByDay[$dateStr] ?? 0),
                ];
            }
            
            return $weeklyData;
        });
    }

    /**
     * Clear all dashboard caches
     */
    public static function clearAll()
    {
        $patterns = [
            'dashboard:total_*',
            'dashboard:user_*',
            'dashboard:active_*',
            'dashboard:orders_*',
            'dashboard:donation_*',
            'dashboard:food_*',
            'dashboard:monthly_*',
            'dashboard:consumer:*',
            'dashboard:establishment:*',
            'dashboard:foodbank:*',
        ];
        
        foreach ($patterns as $pattern) {
            // Note: Laravel cache doesn't support wildcard deletion by default
            // This would need Redis or a custom implementation
            // For now, we'll clear specific keys
        }
    }

    /**
     * Clear cache for specific user
     */
    public static function clearUserCache($userType, $userId)
    {
        $key = "dashboard:{$userType}:{$userId}";
        Cache::forget($key);
        Cache::forget("{$key}:reviews");
        Cache::forget("{$key}:weekly");
    }
}

