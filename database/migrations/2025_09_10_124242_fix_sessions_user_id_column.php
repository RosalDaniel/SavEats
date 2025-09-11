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
        // Use raw SQL to convert bigint to uuid
        DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE uuid USING user_id::text::uuid');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Revert user_id back to bigint
            $table->bigInteger('user_id')->nullable()->change();
        });
    }
};
