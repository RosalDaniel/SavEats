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
        Schema::table('establishments', function (Blueprint $table) {
            $table->boolean('verified')->default(false)->after('status');
            $table->integer('violations_count')->default(0)->after('verified');
            $table->text('violations')->nullable()->after('violations_count'); // JSON array of violation records
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn(['verified', 'violations_count', 'violations']);
        });
    }
};
