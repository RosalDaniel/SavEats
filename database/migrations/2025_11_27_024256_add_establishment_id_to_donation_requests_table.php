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
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->uuid('establishment_id')->nullable()->after('foodbank_id');
            $table->string('unit')->nullable()->after('quantity');
            $table->date('expiry_date')->nullable()->after('description');
            $table->date('scheduled_date')->nullable()->after('expiry_date');
            $table->time('scheduled_time')->nullable()->after('scheduled_date');
            $table->string('pickup_method')->nullable()->after('scheduled_time');
            $table->text('establishment_notes')->nullable()->after('pickup_method');
            
            $table->foreign('establishment_id')->references('establishment_id')->on('establishments')->onDelete('cascade');
            $table->index('establishment_id');
        });
        
        // Update status column to allow new values (PostgreSQL compatible)
        \DB::statement("ALTER TABLE donation_requests DROP CONSTRAINT IF EXISTS donation_requests_status_check");
        \DB::statement("ALTER TABLE donation_requests ADD CONSTRAINT donation_requests_status_check CHECK (status IN ('pending', 'active', 'completed', 'expired', 'accepted', 'declined'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->dropForeign(['establishment_id']);
            $table->dropIndex(['establishment_id']);
            $table->dropColumn([
                'establishment_id',
                'unit',
                'expiry_date',
                'scheduled_date',
                'scheduled_time',
                'pickup_method',
                'establishment_notes'
            ]);
        });
        
        // Restore original status constraint
        \DB::statement("ALTER TABLE donation_requests DROP CONSTRAINT IF EXISTS donation_requests_status_check");
        \DB::statement("ALTER TABLE donation_requests ADD CONSTRAINT donation_requests_status_check CHECK (status IN ('pending', 'active', 'completed', 'expired'))");
    }
};
