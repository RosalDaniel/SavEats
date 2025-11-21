<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update orders table - make columns nullable and change foreign keys to set null
        Schema::table('orders', function (Blueprint $table) {
            // Drop existing foreign keys
            $table->dropForeign(['consumer_id']);
            $table->dropForeign(['establishment_id']);
        });

        // Make columns nullable
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('consumer_id')->nullable()->change();
            $table->uuid('establishment_id')->nullable()->change();
        });

        // Recreate foreign keys with set null
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('consumer_id')
                  ->references('consumer_id')
                  ->on('consumers')
                  ->onDelete('set null');
            
            $table->foreign('establishment_id')
                  ->references('establishment_id')
                  ->on('establishments')
                  ->onDelete('set null');
        });

        // Update donations table - make columns nullable and change foreign keys
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign(['foodbank_id']);
            $table->dropForeign(['establishment_id']);
        });

        // Make columns nullable
        Schema::table('donations', function (Blueprint $table) {
            $table->uuid('foodbank_id')->nullable()->change();
            $table->uuid('establishment_id')->nullable()->change();
        });

        // Recreate foreign keys with set null
        Schema::table('donations', function (Blueprint $table) {
            $table->foreign('foodbank_id')
                  ->references('foodbank_id')
                  ->on('foodbanks')
                  ->onDelete('set null');
            
            $table->foreign('establishment_id')
                  ->references('establishment_id')
                  ->on('establishments')
                  ->onDelete('set null');
        });

        // Update donation_requests table - make column nullable and change foreign key
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->dropForeign(['foodbank_id']);
        });

        // Make column nullable
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->uuid('foodbank_id')->nullable()->change();
        });

        // Recreate foreign key with set null
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->foreign('foodbank_id')
                  ->references('foodbank_id')
                  ->on('foodbanks')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert orders table foreign keys back to cascade
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['consumer_id']);
            $table->dropForeign(['establishment_id']);
        });

        // Make columns not nullable
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('consumer_id')->nullable(false)->change();
            $table->uuid('establishment_id')->nullable(false)->change();
        });

        // Recreate foreign keys with cascade
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('consumer_id')
                  ->references('consumer_id')
                  ->on('consumers')
                  ->onDelete('cascade');
            
            $table->foreign('establishment_id')
                  ->references('establishment_id')
                  ->on('establishments')
                  ->onDelete('cascade');
        });

        // Revert donations table foreign keys
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign(['foodbank_id']);
            $table->dropForeign(['establishment_id']);
        });

        // Make columns not nullable
        Schema::table('donations', function (Blueprint $table) {
            $table->uuid('foodbank_id')->nullable(false)->change();
            $table->uuid('establishment_id')->nullable(false)->change();
        });

        // Recreate foreign keys with cascade
        Schema::table('donations', function (Blueprint $table) {
            $table->foreign('foodbank_id')
                  ->references('foodbank_id')
                  ->on('foodbanks')
                  ->onDelete('cascade');
            
            $table->foreign('establishment_id')
                  ->references('establishment_id')
                  ->on('establishments')
                  ->onDelete('cascade');
        });

        // Revert donation_requests table foreign keys
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->dropForeign(['foodbank_id']);
        });

        // Make column not nullable
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->uuid('foodbank_id')->nullable(false)->change();
        });

        // Recreate foreign key with cascade
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->foreign('foodbank_id')
                  ->references('foodbank_id')
                  ->on('foodbanks')
                  ->onDelete('cascade');
        });
    }
};

