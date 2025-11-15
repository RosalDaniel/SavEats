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
        Schema::create('donations', function (Blueprint $table) {
            $table->uuid('donation_id')->primary();
            $table->uuid('foodbank_id');
            $table->uuid('establishment_id');
            $table->uuid('donation_request_id')->nullable(); // Link to original request if applicable
            $table->string('donation_number')->unique(); // Human-readable donation ID
            $table->string('item_name');
            $table->string('item_category');
            $table->integer('quantity');
            $table->string('unit')->default('pcs'); // pcs, kg, liters, etc.
            $table->text('description')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['pending_pickup', 'ready_for_collection', 'collected', 'cancelled', 'expired'])->default('pending_pickup');
            $table->enum('pickup_method', ['pickup', 'delivery'])->default('pickup');
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->string('handler_name')->nullable(); // Volunteer/staff who handled pickup
            $table->text('establishment_notes')->nullable();
            $table->text('foodbank_notes')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->boolean('is_nearing_expiry')->default(false);
            $table->timestamps();

            $table->foreign('foodbank_id')->references('foodbank_id')->on('foodbanks')->onDelete('cascade');
            $table->foreign('establishment_id')->references('establishment_id')->on('establishments')->onDelete('cascade');
            $table->foreign('donation_request_id')->references('donation_request_id')->on('donation_requests')->onDelete('set null');
            
            $table->index('foodbank_id');
            $table->index('establishment_id');
            $table->index('status');
            $table->index('scheduled_date');
            $table->index('item_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
