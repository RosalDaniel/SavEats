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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->uuid('consumer_id');
            $table->foreign('consumer_id')->references('consumer_id')->on('consumers')->onDelete('cascade');
            $table->foreignId('food_listing_id')->constrained('food_listings')->onDelete('cascade');
            $table->uuid('establishment_id');
            $table->foreign('establishment_id')->references('establishment_id')->on('establishments')->onDelete('cascade');
            $table->integer('rating'); // 1-5 stars
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('video_path')->nullable();
            $table->timestamps();
            
            // Ensure one review per order
            $table->unique('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
