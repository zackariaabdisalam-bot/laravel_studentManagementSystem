<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {

            $table->foreignId('fee_structure_id')
                  ->nullable()
                  ->after('student_id')
                  ->constrained('fee_structures')
                  ->nullOnDelete();

            $table->string('mpesa_phone')->nullable();

            $table->string('transaction_reference')
                  ->nullable()
                  ->unique();

            $table->string('merchant_request_id')
                  ->nullable()
                  ->index();

            $table->string('checkout_request_id')
                  ->nullable()
                  ->unique();

            $table->string('mpesa_receipt_number')
                  ->nullable()
                  ->unique();

            $table->string('receipt_number')
                  ->nullable()
                  ->unique();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {

            $table->dropForeign(['fee_structure_id']);

            $table->dropUnique([
                'payments_transaction_reference_unique'
            ]);

            $table->dropIndex([
                'payments_merchant_request_id_index'
            ]);

            $table->dropUnique([
                'payments_checkout_request_id_unique'
            ]);

            $table->dropUnique([
                'payments_mpesa_receipt_number_unique'
            ]);

            $table->dropUnique([
                'payments_receipt_number_unique'
            ]);

            $table->dropColumn([
                'fee_structure_id',
                'mpesa_phone',
                'transaction_reference',
                'merchant_request_id',
                'checkout_request_id',
                'mpesa_receipt_number',
                'receipt_number',
            ]);
        });
    }
};