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
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('stock_deducted')->default(false)->after('status');
            $table->timestamp('stock_deducted_at')->nullable()->after('stock_deducted');
            $table->boolean('stock_restored')->default(false)->after('stock_deducted_at');
            $table->timestamp('stock_restored_at')->nullable()->after('stock_restored');
            $table->string('payment_status')->default('pending')->after('payment_method'); // 'pending', 'confirmed', 'failed'
            $table->timestamp('payment_confirmed_at')->nullable()->after('payment_status');
            
            $table->index(['stock_deducted', 'payment_status']);
            $table->index(['stock_restored', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['stock_deducted', 'payment_status']);
            $table->dropIndex(['stock_restored', 'status']);
            $table->dropColumn([
                'stock_deducted',
                'stock_deducted_at',
                'stock_restored',
                'stock_restored_at',
                'payment_status',
                'payment_confirmed_at'
            ]);
        });
    }
};
