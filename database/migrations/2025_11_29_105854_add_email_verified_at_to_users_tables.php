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
        // Add email_verified_at to consumers table
        if (!Schema::hasColumn('consumers', 'email_verified_at')) {
            Schema::table('consumers', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            });
        }

        // Add email_verified_at to establishments table
        if (!Schema::hasColumn('establishments', 'email_verified_at')) {
            Schema::table('establishments', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            });
        }

        // Add email_verified_at to foodbanks table
        if (!Schema::hasColumn('foodbanks', 'email_verified_at')) {
            Schema::table('foodbanks', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumers', function (Blueprint $table) {
            if (Schema::hasColumn('consumers', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });

        Schema::table('establishments', function (Blueprint $table) {
            if (Schema::hasColumn('establishments', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });

        Schema::table('foodbanks', function (Blueprint $table) {
            if (Schema::hasColumn('foodbanks', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });
    }
};
