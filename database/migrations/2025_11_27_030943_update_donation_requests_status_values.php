<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update status column to allow new values (PostgreSQL compatible)
        \DB::statement("ALTER TABLE donation_requests DROP CONSTRAINT IF EXISTS donation_requests_status_check");
        \DB::statement("ALTER TABLE donation_requests ADD CONSTRAINT donation_requests_status_check CHECK (status IN ('pending', 'active', 'completed', 'expired', 'accepted', 'declined', 'pickup_confirmed', 'delivery_successful'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore previous status constraint
        \DB::statement("ALTER TABLE donation_requests DROP CONSTRAINT IF EXISTS donation_requests_status_check");
        \DB::statement("ALTER TABLE donation_requests ADD CONSTRAINT donation_requests_status_check CHECK (status IN ('pending', 'active', 'completed', 'expired', 'accepted', 'declined'))");
    }
};
