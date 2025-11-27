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
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'user_registered', 'review_flagged', 'donation_issue', 'account_deletion_request', 'system_alert', etc.
            $table->string('title');
            $table->text('message');
            $table->string('priority')->default('normal'); // 'low', 'normal', 'high', 'urgent'
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->unsignedBigInteger('read_by')->nullable(); // Admin user ID who read it
            
            // Related entity references (nullable)
            $table->uuid('user_id')->nullable(); // Related user (consumer_id, establishment_id, foodbank_id)
            $table->string('user_type')->nullable(); // 'consumer', 'establishment', 'foodbank'
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('review_id')->nullable();
            $table->uuid('donation_id')->nullable();
            $table->uuid('donation_request_id')->nullable();
            $table->unsignedBigInteger('deletion_request_id')->nullable();
            
            // Additional data stored as JSON for flexibility
            $table->json('data')->nullable();
            
            // Soft deletes
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['is_read', 'priority']);
            $table->index('type');
            $table->index('created_at');
            $table->index('read_by');
            
            // Foreign keys
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('review_id')->references('id')->on('reviews')->onDelete('cascade');
            $table->foreign('donation_id')->references('donation_id')->on('donations')->onDelete('cascade');
            $table->foreign('donation_request_id')->references('donation_request_id')->on('donation_requests')->onDelete('cascade');
            // Note: read_by references users.id but foreign key is optional since users table may not exist
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
