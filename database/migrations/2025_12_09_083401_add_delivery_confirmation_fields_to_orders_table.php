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
        // Skip if all columns already exist to avoid duplicate-column errors on redeploy
        if (Schema::hasColumns('orders', [
            'out_for_delivery_at',
            'admin_intervention_requested_at',
            'admin_intervention_reason',
        ])) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'out_for_delivery_at')) {
                $table->timestamp('out_for_delivery_at')->nullable()->after('accepted_at');
            }
            if (!Schema::hasColumn('orders', 'admin_intervention_requested_at')) {
                $table->timestamp('admin_intervention_requested_at')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('orders', 'admin_intervention_reason')) {
                $table->text('admin_intervention_reason')->nullable()->after('admin_intervention_requested_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach ([
                'out_for_delivery_at',
                'admin_intervention_requested_at',
                'admin_intervention_reason',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $columnsToDrop[] = $column;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
