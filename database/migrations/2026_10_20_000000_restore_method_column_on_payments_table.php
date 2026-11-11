<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('payments', 'payment_method') && ! Schema::hasColumn('payments', 'method')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->renameColumn('payment_method', 'method');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payments', 'method') && ! Schema::hasColumn('payments', 'payment_method')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->renameColumn('method', 'payment_method');
            });
        }
    }
};
