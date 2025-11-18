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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // login_attempt, password_reset, role_change, suspicious_activity, etc.
            $table->string('severity')->default('info'); // info, warning, error, critical
            $table->string('user_type')->nullable(); // consumer, establishment, foodbank, admin
            $table->string('user_id')->nullable(); // UUID or ID of the user
            $table->string('user_email')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('action')->nullable(); // e.g., 'login_success', 'login_failed', 'password_changed'
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional data in JSON format
            $table->string('status')->default('success'); // success, failed, blocked
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('event_type');
            $table->index('severity');
            $table->index('user_type');
            $table->index('user_id');
            $table->index('ip_address');
            $table->index('created_at');
            $table->index(['event_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
