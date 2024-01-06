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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable(false);
            $table->unsignedBigInteger('product_id')->nullable(false);
            $table->string('product_name')->nullable(false);
            $table->string('product_image')->nullable();
            $table->string('product_description', 100)->nullable();
            $table->decimal('product_price', 10, 2);
            $table->boolean('product_is_for_sale', true)->nullable(false);
            $table->integer('qty')->nullable(false);
            $table->integer('discount')->nullable(false)->default(0);
            $table->decimal('subtotal')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
