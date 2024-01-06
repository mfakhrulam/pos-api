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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->text('invoice')->nullable(false);
            $table->string('payment_type', 50)->nullable(false);
            $table->decimal('discount')->default(0);
            $table->decimal('tax')->default(0);
            $table->decimal('total')->nullable(false);
            $table->decimal('total_paid')->nullable(false);
            $table->decimal('return')->default(0);
            $table->unsignedBigInteger('outlet_id')->nullable(false);
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable(false);
            $table->boolean('is_paid')->nullable(false);
            $table->boolean('is_refunded')->nullable(false);

            $table->timestamps();

            $table->foreign('outlet_id' )->on('outlets')->references('id')->onDelete('cascade');
            $table->foreign('customer_id' )->on('customers')->references('id')->onDelete('cascade');
            $table->foreign('employee_id' )->on('employees')->references('id')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
