<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFavoriteCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('favorite_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('favorite_categories');
    }
}