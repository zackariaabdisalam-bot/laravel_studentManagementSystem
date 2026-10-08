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
    Schema::create('payments', function (Blueprint $table) {
        $table->id();

        $table->foreignId('student_id')
              ->constrained('students')
              ->onDelete('cascade');

        $table->foreignId('fee_id')
              ->constrained('fees')
              ->onDelete('cascade');

        $table->decimal('amount', 12, 2);
        $table->string('method');
        $table->string('reference')->nullable()->unique();
        $table->date('paid_at');
        $table->foreignId('received_by')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
