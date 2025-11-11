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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->uuid('chapter_uuid')->unique();
            $table->string('name'); // e.g., "Ghana Chapter", "New York Chapter"
            $table->string('code')->unique(); // e.g., "GH", "US-NY"
            $table->text('description')->nullable();
            $table->string('type'); // 'country' or 'city'
            $table->string('country_code', 2); // ISO country code
            $table->string('country_name');
            $table->string('state_province')->nullable(); // State/Province name
            $table->string('city')->nullable(); // City name
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['country_code', 'type']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};

