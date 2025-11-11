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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // Mr, Mrs, Dr, Prof, etc. - REMOVED after('id')
            $table->string('first_name');
            $table->string('last_name');
            $table->string('other_names')->nullable();
            $table->string('maiden_name')->nullable();
            $table->date('dob');
            $table->string('year_of_graduation')->nullable();
            $table->string('program_of_study')->nullable();
            $table->string('phone_number')->unique();
            $table->string('country_of_residence')->nullable();
            $table->string('nationality')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('linkedin_profile')->nullable();
            $table->string('personal_website')->nullable();
            $table->string('password');
            $table->text('bio')->nullable();
            $table->string('ug_student_id_number')->nullable();
            $table->string('hall_of_residence')->nullable();
            $table->boolean('share_with_alumni_associations')->default(false);
            $table->boolean('include_in_birthday_list')->default(false);
            $table->boolean('receive_newsletter')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
