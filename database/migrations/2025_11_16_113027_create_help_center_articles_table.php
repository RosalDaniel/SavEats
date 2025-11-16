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
        Schema::create('help_center_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->string('category')->nullable();
            $table->text('tags')->nullable(); // JSON array or comma-separated
            $table->integer('view_count')->default(0);
            $table->enum('status', ['published', 'draft', 'archived'])->default('draft');
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index('status');
            $table->index('category');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_center_articles');
    }
};

