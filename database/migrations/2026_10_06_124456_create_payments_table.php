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

        $table->foreignId('fee_structure_id')
              ->constrained('fee_structures')
              ->onDelete('cascade');

        $table->decimal('amount', 12, 2);
        $table->string('payment_method');
        $table->string('transaction_reference')->unique();
        $table->date('payment_date');
        $table->string('received_by')
              ->constrained('users')
              ->onDelete('cascade');

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
