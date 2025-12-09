<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop delivery_option column from donation_requests table
        if (Schema::hasColumn('donation_requests', 'delivery_option')) {
            Schema::table('donation_requests', function (Blueprint $table) {
                $table->dropColumn('delivery_option');
            });
        }

        // Update pickup_method enum in donations table to only allow 'pickup'
        // First, update any existing 'delivery' values to 'pickup'
        DB::statement("UPDATE donations SET pickup_method = 'pickup' WHERE pickup_method = 'delivery'");
        
        // Drop the existing enum constraint and recreate with only 'pickup'
        // Note: PostgreSQL doesn't support ALTER ENUM directly, so we need to recreate the column
        if (Schema::hasColumn('donations', 'pickup_method')) {
            Schema::table('donations', function (Blueprint $table) {
                $table->dropColumn('pickup_method');
            });
            
            Schema::table('donations', function (Blueprint $table) {
                $table->enum('pickup_method', ['pickup'])->default('pickup')->after('status');
            });
        }

        // Update status constraint in donation_requests to remove delivery_successful
        DB::statement("ALTER TABLE donation_requests DROP CONSTRAINT IF EXISTS donation_requests_status_check");
        DB::statement("ALTER TABLE donation_requests ADD CONSTRAINT donation_requests_status_check CHECK (status IN ('pending', 'pending_confirmation', 'active', 'completed', 'expired', 'accepted', 'declined', 'pickup_confirmed'))");
        
        // Update any delivery_successful statuses to completed
        DB::statement("UPDATE donation_requests SET status = 'completed' WHERE status = 'delivery_successful'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore delivery_option column
        if (!Schema::hasColumn('donation_requests', 'delivery_option')) {
            Schema::table('donation_requests', function (Blueprint $table) {
                $table->enum('delivery_option', ['pickup', 'delivery'])->default('pickup')->after('address');
            });
        }

        // Restore pickup_method enum with both options
        if (Schema::hasColumn('donations', 'pickup_method')) {
            Schema::table('donations', function (Blueprint $table) {
                $table->dropColumn('pickup_method');
            });
            
            Schema::table('donations', function (Blueprint $table) {
                $table->enum('pickup_method', ['pickup', 'delivery'])->default('pickup')->after('status');
            });
        }

        // Restore status constraint with delivery_successful
        DB::statement("ALTER TABLE donation_requests DROP CONSTRAINT IF EXISTS donation_requests_status_check");
        DB::statement("ALTER TABLE donation_requests ADD CONSTRAINT donation_requests_status_check CHECK (status IN ('pending', 'pending_confirmation', 'active', 'completed', 'expired', 'accepted', 'declined', 'pickup_confirmed', 'delivery_successful'))");
    }
};
