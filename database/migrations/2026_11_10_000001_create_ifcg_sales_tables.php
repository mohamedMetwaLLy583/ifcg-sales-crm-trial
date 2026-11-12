<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up() {
  Schema::create('ifcg_inquiries', function(Blueprint $table) {
   $table->id(); $table->string('reference')->unique(); $table->string('company');
   $table->string('contact'); $table->string('email'); $table->string('phone')->nullable();
   $table->string('country'); $table->string('channel'); $table->string('locale', 5);
   $table->json('items'); $table->text('message')->nullable(); $table->string('status')->default('new');
   $table->foreignId('owner_id')->nullable()->constrained('users'); $table->date('follow_up_at')->nullable();
   $table->text('internal_notes')->nullable(); $table->timestamps();
  });
  Schema::create('ifcg_quotes', function(Blueprint $table) {
   $table->id(); $table->foreignId('inquiry_id')->constrained('ifcg_inquiries');
   $table->string('reference')->unique(); $table->integer('version'); $table->string('locale',5);
   $table->json('customer_snapshot'); $table->json('items'); $table->string('currency',3)->default('EGP');
   $table->bigInteger('total_minor'); $table->date('valid_until'); $table->text('terms');
   $table->string('status')->default('draft'); $table->foreignId('created_by')->constrained('users');
   $table->timestamps(); $table->unique(['inquiry_id','version']);
  });
 }
 public function down() { Schema::dropIfExists('ifcg_quotes'); Schema::dropIfExists('ifcg_inquiries'); }
};

