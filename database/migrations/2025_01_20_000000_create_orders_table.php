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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->uuid('consumer_id');
            $table->foreign('consumer_id')->references('consumer_id')->on('consumers')->onDelete('cascade');
            $table->uuid('establishment_id');
            $table->foreign('establishment_id')->references('establishment_id')->on('establishments')->onDelete('cascade');
            $table->foreignId('food_listing_id')->constrained('food_listings')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('delivery_method'); // 'pickup' or 'delivery'
            $table->string('payment_method'); // 'cash', 'card', 'ewallet'
            $table->string('status')->default('pending'); // 'pending', 'accepted', 'completed', 'cancelled'
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('delivery_address')->nullable();
            $table->time('pickup_start_time')->nullable();
            $table->time('pickup_end_time')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
