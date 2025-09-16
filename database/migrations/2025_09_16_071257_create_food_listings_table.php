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
        Schema::create('food_listings', function (Blueprint $table) {
            $table->id();
            $table->uuid('establishment_id');
            $table->foreign('establishment_id')->references('establishment_id')->on('establishments')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->integer('quantity');
            $table->decimal('original_price', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discounted_price', 10, 2)->nullable();
            $table->date('expiry_date');
            $table->string('address')->nullable();
            $table->boolean('pickup_available')->default(false);
            $table->boolean('delivery_available')->default(false);
            $table->string('image_path')->nullable();
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_listings');
    }
};
