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
        Schema::create('consumers', function (Blueprint $table) {
            $table->uuid('consumer_id')->primary();
            $table->string('fname');
            $table->string('lname');
            $table->string('mname')->nullable();
            $table->string('email')->unique();
            $table->string('phone_no')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumers');
    }
};
