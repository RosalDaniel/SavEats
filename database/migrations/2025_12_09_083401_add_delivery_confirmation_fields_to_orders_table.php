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
            $table->timestamp('out_for_delivery_at')->nullable()->after('accepted_at');
            $table->timestamp('admin_intervention_requested_at')->nullable()->after('completed_at');
            $table->text('admin_intervention_reason')->nullable()->after('admin_intervention_requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'out_for_delivery_at',
                'admin_intervention_requested_at',
                'admin_intervention_reason'
            ]);
        });
    }
};
