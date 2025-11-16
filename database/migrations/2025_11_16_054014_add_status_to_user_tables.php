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
        // Add status to consumers table
        Schema::table('consumers', function (Blueprint $table) {
            $table->enum('status', ['active', 'suspended', 'deleted'])->default('active')->after('registered_at');
        });

        // Add status to establishments table
        Schema::table('establishments', function (Blueprint $table) {
            $table->enum('status', ['active', 'suspended', 'deleted'])->default('active')->after('registered_at');
        });

        // Add status to foodbanks table
        Schema::table('foodbanks', function (Blueprint $table) {
            $table->enum('status', ['active', 'suspended', 'deleted'])->default('active')->after('registered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumers', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('foodbanks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
