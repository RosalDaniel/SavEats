<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\DonationRequest;
use App\Observers\DonationRequestObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        DonationRequest::observe(DonationRequestObserver::class);
    }
}
