<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->nullable()->unique(); // staff login
    $table->string('password');

    $table->enum('user_type', ['applicant','student','staff']);

    $table->string('registration_number')->nullable()->unique(); // student login
    $table->string('phone')->nullable();
    $table->string('gender')->nullable();
    $table->string('profile_photo')->nullable();

    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
