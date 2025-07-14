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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->comment('Unique identifier for the order');
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->decimal('subtotal', 15, 2)->comment('Subtotal amount for the order');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Discount amount applied to the order');
            $table->decimal('shipping_cost', 15, 2)->default(0)->comment('Shipping cost for the order');
            $table->decimal('total_amount', 15, 2)->comment('Total amount for the order');
            $table->string('shipping_address')->comment('Shipping address for the order');
            $table->string('phone_number')->nullable()->comment('Phone number for the order');
            $table->string('notes')->nullable()->comment('Additional notes for the order');
            $table->enum('status', ['PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELED'])
                ->default('PENDING')
                ->comment('Status of the order');
            $table->timestamp('paid_at')->nullable()->comment('Timestamp when the order was paid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
