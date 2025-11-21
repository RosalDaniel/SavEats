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
        Schema::create('stock_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_listing_id')->constrained('food_listings')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->enum('transaction_type', ['deduction', 'restoration'])->index();
            $table->integer('quantity');
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->integer('reserved_stock_before')->default(0);
            $table->integer('reserved_stock_after')->default(0);
            $table->integer('sold_stock_before')->default(0);
            $table->integer('sold_stock_after')->default(0);
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['food_listing_id', 'created_at']);
            $table->index(['order_id', 'transaction_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledger');
    }
};
