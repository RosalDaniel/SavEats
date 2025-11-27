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
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->uuid('fulfilled_by_establishment_id')->nullable()->after('matches');
            $table->timestamp('fulfilled_at')->nullable()->after('fulfilled_by_establishment_id');
            $table->uuid('donation_id')->nullable()->after('fulfilled_at');
            
            $table->foreign('fulfilled_by_establishment_id')
                  ->references('establishment_id')
                  ->on('establishments')
                  ->onDelete('set null');
            
            $table->foreign('donation_id')
                  ->references('donation_id')
                  ->on('donations')
                  ->onDelete('set null');
            
            $table->index('fulfilled_by_establishment_id');
            $table->index('donation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->dropForeign(['fulfilled_by_establishment_id']);
            $table->dropForeign(['donation_id']);
            $table->dropIndex(['fulfilled_by_establishment_id']);
            $table->dropIndex(['donation_id']);
            $table->dropColumn(['fulfilled_by_establishment_id', 'fulfilled_at', 'donation_id']);
        });
    }
};
