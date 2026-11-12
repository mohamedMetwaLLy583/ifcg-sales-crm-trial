<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique()->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // جدول السمات مع نوع الإدخال
        Schema::create('variation_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('display_name');
            $table->enum('input_type', ['select', 'text', 'color'])->default('select');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('name');
        });

        // جدول القيم المحددة مسبقاً للسمات
        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('variation_attributes')->onDelete('cascade');
            $table->string('value');
            $table->string('display_value');
            $table->string('color_code')->nullable(); 
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['attribute_id', 'value']);
        });

        // جدول قيم السمات للمتغيرات
        Schema::create('variation_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variation_id')->constrained('product_variations')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('variation_attributes')->onDelete('cascade');
            $table->foreignId('option_id')->nullable()->constrained('attribute_options')->onDelete('cascade');
            $table->string('value');
            $table->timestamps();

            $table->unique(['variation_id', 'attribute_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('variation_attribute_values');
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('variation_attributes');
        Schema::dropIfExists('product_variations');
    }
};