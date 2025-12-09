<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DashboardCacheService;
use App\Models\Consumer;
use App\Models\Establishment;
use App\Models\Foodbank;
use Illuminate\Support\Facades\Cache;

class PrecomputeDashboardMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:precompute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Precompute dashboard metrics and store them in cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Precomputing dashboard metrics...');

        // Precompute admin dashboard metrics
        $this->info('Precomputing admin dashboard metrics...');
        DashboardCacheService::getTotalUsers();
        DashboardCacheService::getUserCounts();
        DashboardCacheService::getTotalListings();
        DashboardCacheService::getActiveListings();
        DashboardCacheService::getTotalOrders();
        DashboardCacheService::getOrdersByStatus();
        DashboardCacheService::getTotalDonations();
        DashboardCacheService::getDonationStatuses();
        DashboardCacheService::getFoodRescued();
        DashboardCacheService::getMonthlyActivity();
        
        // Precompute engagement data
        Cache::remember('dashboard:admin:engagement', DashboardCacheService::CACHE_TTL * 60, function () {
            $totalUsers = DashboardCacheService::getTotalUsers();
            $thirtyDaysAgo = \Carbon\Carbon::now()->subDays(30);
            $activeConsumers = Consumer::where('created_at', '>=', $thirtyDaysAgo)->count();
            $activeEstablishments = Establishment::where('created_at', '>=', $thirtyDaysAgo)->count();
            $activeFoodbanks = Foodbank::where('created_at', '>=', $thirtyDaysAgo)->count();
            $totalActiveUsers = $activeConsumers + $activeEstablishments + $activeFoodbanks;
            $engagementPercentage = $totalUsers > 0 ? round(($totalActiveUsers / $totalUsers) * 100) : 0;
            
            return [
                'active_consumers' => $activeConsumers,
                'active_establishments' => $activeEstablishments,
                'active_foodbanks' => $activeFoodbanks,
                'total_active_users' => $totalActiveUsers,
                'engagement_percentage' => $engagementPercentage,
            ];
        });

        // Precompute consumer dashboard metrics for all consumers
        $this->info('Precomputing consumer dashboard metrics...');
        $consumers = Consumer::pluck('consumer_id');
        $bar = $this->output->createProgressBar($consumers->count());
        $bar->start();
        
        foreach ($consumers as $consumerId) {
            DashboardCacheService::getConsumerStats($consumerId);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        // Precompute establishment dashboard metrics for all establishments
        $this->info('Precomputing establishment dashboard metrics...');
        $establishments = Establishment::pluck('establishment_id');
        $bar = $this->output->createProgressBar($establishments->count());
        $bar->start();
        
        foreach ($establishments as $establishmentId) {
            DashboardCacheService::getEstablishmentStats($establishmentId);
            DashboardCacheService::getEstablishmentReviews($establishmentId);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        // Precompute foodbank dashboard metrics for all foodbanks
        $this->info('Precomputing foodbank dashboard metrics...');
        $foodbanks = Foodbank::pluck('foodbank_id');
        $bar = $this->output->createProgressBar($foodbanks->count());
        $bar->start();
        
        foreach ($foodbanks as $foodbankId) {
            DashboardCacheService::getFoodbankStats($foodbankId);
            DashboardCacheService::getFoodbankWeeklyData($foodbankId);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        // Precompute best deals for consumer dashboard
        $this->info('Precomputing best deals...');
        Cache::remember('dashboard:consumer:best_deals', 5 * 60, function () {
            return \App\Models\FoodListing::where('status', 'active')
                ->where('discount_percentage', '>', 0)
                ->where('expiry_date', '>=', now()->toDateString())
                ->orderBy('discount_percentage', 'desc')
                ->inRandomOrder()
                ->limit(2)
                ->get()
                ->map(function ($item) {
                    $availableStock = max(0, $item->quantity - ($item->reserved_stock ?? 0));
                    $discountedPrice = $item->original_price * (1 - ($item->discount_percentage / 100));
                    
                    $imageUrl = null;
                    if ($item->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->image_path)) {
                        $imageUrl = \Illuminate\Support\Facades\Storage::url($item->image_path);
                    }
                    
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'quantity' => $availableStock,
                        'original_price' => $item->original_price,
                        'discounted_price' => $discountedPrice,
                        'discount_percentage' => round($item->discount_percentage),
                        'image_url' => $imageUrl,
                    ];
                });
        });

        $this->info('Dashboard metrics precomputed successfully!');
        return 0;
    }
}

