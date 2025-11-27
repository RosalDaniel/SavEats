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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'order_placed', 'order_accepted', 'order_cancelled', 'order_completed', 'donation_requested', 'donation_approved', etc.
            $table->string('title');
            $table->text('message');
            $table->string('user_type'); // 'consumer', 'establishment', 'foodbank'
            $table->uuid('user_id'); // The ID of the user receiving the notification (consumer_id, establishment_id, or foodbank_id)
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            // Related entity references (nullable, for linking to orders, donations, etc.)
            $table->unsignedBigInteger('order_id')->nullable();
            $table->uuid('donation_id')->nullable();
            $table->uuid('donation_request_id')->nullable();
            
            // Additional data stored as JSON for flexibility
            $table->json('data')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_type', 'user_id']);
            $table->index(['user_type', 'user_id', 'is_read']);
            $table->index('order_id');
            $table->index('donation_id');
            $table->index('created_at');
            
            // Foreign keys
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('donation_id')->references('donation_id')->on('donations')->onDelete('cascade');
            $table->foreign('donation_request_id')->references('donation_request_id')->on('donation_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
