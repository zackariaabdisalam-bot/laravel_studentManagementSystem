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
    Schema::create('fee_structures', function (Blueprint $table) {
        $table->id();

        $table->foreignId('course_id')
              ->constrained('courses')
              ->onDelete('cascade');

        $table->foreignId('semester_id')
              ->constrained('semesters_years')
              ->onDelete('cascade');

        $table->foreignId('academic_year_id')
              ->constrained('academic_years')
              ->onDelete('cascade');

        $table->decimal('amount', 12, 2);

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
