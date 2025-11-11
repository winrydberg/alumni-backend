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
        Schema::create('student_degrees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('degree_received'); // E.g., BA Fine Art, BSc Computer Science, MBA
            $table->string('institution')->default('University of Ghana'); // Institution name
            $table->year('year_of_completion')->nullable();
            $table->string('college')->nullable(); // E.g., College of Humanities
            $table->string('department')->nullable(); // E.g., Department of Fine Art
            $table->enum('degree_level', ['diploma', 'bachelor', 'master', 'phd', 'certificate', 'other'])->nullable();
            $table->string('classification')->nullable(); // E.g., First Class, Second Class Upper
            $table->boolean('is_primary')->default(false); // Mark primary/most recent degree
            $table->timestamps();

            // Add index for faster queries
            $table->index('user_id');
            $table->index(['user_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_degrees');
    }
};
