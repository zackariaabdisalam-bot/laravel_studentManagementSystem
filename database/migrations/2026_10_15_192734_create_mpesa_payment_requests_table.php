<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_payment_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fee_id')
                  ->constrained('fees')
                  ->cascadeOnDelete();

            $table->foreignId('payment_id')
                  ->nullable()
                  ->constrained('payments')
                  ->nullOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('phone', 12);
            $table->string('account_reference')->unique();
            $table->string('merchant_request_id')->nullable()->unique();
            $table->string('checkout_request_id')->nullable()->unique();
            $table->string('status')->default('pending');
            $table->text('result_description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_payment_requests');
    }
};