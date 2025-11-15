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
        Schema::create('donation_requests', function (Blueprint $table) {
            $table->uuid('donation_request_id')->primary();
            $table->uuid('foodbank_id');
            $table->string('item_name');
            $table->integer('quantity');
            $table->string('category');
            $table->text('description')->nullable();
            $table->string('distribution_zone');
            $table->date('dropoff_date');
            $table->enum('time_option', ['allDay', 'anytime', 'specific'])->default('allDay');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('address');
            $table->enum('delivery_option', ['pickup', 'delivery'])->default('pickup');
            $table->string('contact_name');
            $table->string('phone_number');
            $table->string('email');
            $table->enum('status', ['pending', 'active', 'completed', 'expired'])->default('pending');
            $table->integer('matches')->default(0);
            $table->timestamps();
            
            $table->foreign('foodbank_id')->references('foodbank_id')->on('foodbanks')->onDelete('cascade');
            $table->index('foodbank_id');
            $table->index('status');
            $table->index('dropoff_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_requests');
    }
};
