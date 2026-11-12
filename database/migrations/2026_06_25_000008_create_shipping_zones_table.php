<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shipping_zones')) {
            return;
        }

        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('cities');
            $table->decimal('shipping_cost', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
