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
        // If all columns already exist, skip to avoid duplicate-column errors on re-deploys
        if (Schema::hasColumns('establishments', ['latitude', 'longitude', 'formatted_address'])) {
            return;
        }

        Schema::table('establishments', function (Blueprint $table) {
            // Guard against re-running on environments where these columns already exist
            if (!Schema::hasColumn('establishments', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('address');
            }
            if (!Schema::hasColumn('establishments', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('establishments', 'formatted_address')) {
                $table->text('formatted_address')->nullable()->after('longitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['latitude', 'longitude', 'formatted_address'] as $column) {
                if (Schema::hasColumn('establishments', $column)) {
                    $columnsToDrop[] = $column;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
