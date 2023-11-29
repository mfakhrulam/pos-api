<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('image')->nullable();
            $table->string('description', 100)->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_for_sale', true)->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->unsignedBigInteger('category_id')->nullable();

            $table->timestamps();
            
            $table->foreign('user_id' )->on('users')->references('id')->onDelete('cascade');
            $table->foreign('category_id' )->on('categories')->references('id')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
