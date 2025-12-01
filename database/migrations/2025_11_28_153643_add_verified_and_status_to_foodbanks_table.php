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
        Schema::table('foodbanks', function (Blueprint $table) {
            if (!Schema::hasColumn('foodbanks', 'verified')) {
                $table->boolean('verified')->default(false)->after('registration_number');
            }
            if (!Schema::hasColumn('foodbanks', 'status')) {
                $table->string('status')->default('active')->after('verified');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foodbanks', function (Blueprint $table) {
            $table->dropColumn(['verified', 'status']);
        });
    }
};
