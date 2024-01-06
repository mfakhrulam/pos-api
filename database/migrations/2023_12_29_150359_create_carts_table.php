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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable(false);
            $table->unsignedBigInteger('product_id')->nullable(false);
            $table->integer('qty')->nullable(false);
            $table->integer('discount')->nullable(false)->default(0);
            $table->timestamps();

            $table->foreign('employee_id' )->on('employees')->references('id')->onDelete('cascade');
            $table->foreign('product_id' )->on('products')->references('id')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
