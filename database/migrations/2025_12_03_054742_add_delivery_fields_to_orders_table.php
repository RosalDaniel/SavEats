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
            $table->string('delivery_type')->nullable()->after('delivery_method'); // 'pickup' or 'delivery'
            $table->decimal('delivery_lat', 10, 8)->nullable()->after('delivery_address');
            $table->decimal('delivery_lng', 11, 8)->nullable()->after('delivery_lat');
            $table->decimal('delivery_distance', 8, 2)->nullable()->after('delivery_lng'); // in kilometers
            $table->decimal('delivery_fee', 10, 2)->nullable()->after('delivery_distance');
            $table->string('delivery_eta')->nullable()->after('delivery_fee'); // e.g., "15-25 mins"
            $table->text('delivery_instructions')->nullable()->after('delivery_eta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_type',
                'delivery_lat',
                'delivery_lng',
                'delivery_distance',
                'delivery_fee',
                'delivery_eta',
                'delivery_instructions'
            ]);
        });
    }
};
