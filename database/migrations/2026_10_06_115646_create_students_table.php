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
    Schema::create('students', function (Blueprint $table) {
        $table->id();
        $table->string('student_number')->unique();
        $table->string('first_name');
        $table->string('last_name');
        $table->string('gender')->nullable();
        $table->date('date_of_birth')->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->string('address')->nullable;
        $table->string('status')->default('Active');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
