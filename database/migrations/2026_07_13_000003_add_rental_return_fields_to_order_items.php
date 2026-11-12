<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'rental_status')) {
                $table->string('rental_status', 30)->default('reserved')->after('deposit_amount');
            }

            if (!Schema::hasColumn('order_items', 'rental_returned_at')) {
                $table->timestamp('rental_returned_at')->nullable()->after('rental_status');
            }

            if (!Schema::hasColumn('order_items', 'deposit_status')) {
                $table->string('deposit_status', 30)->default('none')->after('rental_returned_at');
            }

            if (!Schema::hasColumn('order_items', 'deposit_refund_amount')) {
                $table->decimal('deposit_refund_amount', 10, 2)->default(0)->after('deposit_status');
            }

            if (!Schema::hasColumn('order_items', 'rental_notes')) {
                $table->text('rental_notes')->nullable()->after('deposit_refund_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            foreach (['rental_notes', 'deposit_refund_amount', 'deposit_status', 'rental_returned_at', 'rental_status'] as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
