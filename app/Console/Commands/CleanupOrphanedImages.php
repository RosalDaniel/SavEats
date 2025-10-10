<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FoodListing;

class CleanupOrphanedImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned image references in food listings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up orphaned images...');
        
        $count = FoodListing::cleanupOrphanedImages();
        
        $this->info("Cleaned up {$count} orphaned image references.");
        
        return Command::SUCCESS;
    }
}