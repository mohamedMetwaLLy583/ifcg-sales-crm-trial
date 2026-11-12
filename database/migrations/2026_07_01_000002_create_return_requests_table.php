<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('requested');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->string('reason', 160);
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->string('resolution', 160)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
};
