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
        // Drop existing table if it exists (Laravel default)
        Schema::dropIfExists('password_reset_tokens');
        
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('user_type'); // consumer, establishment, foodbank, admin
            $table->string('user_id'); // UUID of the user (or integer ID for admin)
            $table->string('email');
            $table->string('phone_no')->nullable();
            $table->text('token'); // Store hashed token (can be longer than 64 chars)
            $table->string('recovery_method'); // email, sms, email_verification
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            
            $table->index(['user_type', 'user_id']);
            $table->index('email');
            $table->index('expires_at');
            $table->index(['used', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
