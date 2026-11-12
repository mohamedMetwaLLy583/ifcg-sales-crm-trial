<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'order_type')) {
                $table->string('order_type', 20)->default('sale')->after('status');
            }

            if (!Schema::hasColumn('order_items', 'rental_start_date')) {
                $table->date('rental_start_date')->nullable()->after('order_type');
            }

            if (!Schema::hasColumn('order_items', 'rental_end_date')) {
                $table->date('rental_end_date')->nullable()->after('rental_start_date');
            }

            if (!Schema::hasColumn('order_items', 'rental_days')) {
                $table->unsignedInteger('rental_days')->nullable()->after('rental_end_date');
            }

            if (!Schema::hasColumn('order_items', 'deposit_amount')) {
                $table->decimal('deposit_amount', 10, 2)->default(0)->after('rental_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            foreach (['deposit_amount', 'rental_days', 'rental_end_date', 'rental_start_date', 'order_type'] as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
