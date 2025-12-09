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
        // Add verification_status to establishments table
        if (!Schema::hasColumn('establishments', 'verification_status')) {
            Schema::table('establishments', function (Blueprint $table) {
                $table->enum('verification_status', ['verified', 'unverified'])->default('unverified')->after('status');
            });
            
            // Migrate existing verified boolean to verification_status
            DB::statement("UPDATE establishments SET verification_status = CASE WHEN verified = true THEN 'verified' ELSE 'unverified' END");
        }
        
        // Add verification_status to foodbanks table
        if (!Schema::hasColumn('foodbanks', 'verification_status')) {
            Schema::table('foodbanks', function (Blueprint $table) {
                $table->enum('verification_status', ['verified', 'unverified'])->default('unverified')->after('status');
            });
            
            // Migrate existing verified boolean to verification_status
            DB::statement("UPDATE foodbanks SET verification_status = CASE WHEN verified = true THEN 'verified' ELSE 'unverified' END");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            if (Schema::hasColumn('establishments', 'verification_status')) {
                $table->dropColumn('verification_status');
            }
        });
        
        Schema::table('foodbanks', function (Blueprint $table) {
            if (Schema::hasColumn('foodbanks', 'verification_status')) {
                $table->dropColumn('verification_status');
            }
        });
    }
};
