<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('ifcg_slides',function(Blueprint $table){$table->id();$table->string('locale',10)->nullable();$table->string('title',180)->nullable();$table->text('description')->nullable();$table->string('button_text',80)->nullable();$table->string('button_url',255)->default('/request-quote');$table->string('media_path');$table->string('media_type',10)->default('image');$table->string('poster_path')->nullable();$table->unsignedInteger('sort_order')->default(0);$table->boolean('is_active')->default(false);$table->timestamps();}); }
 public function down(): void { Schema::dropIfExists('ifcg_slides'); }
};
