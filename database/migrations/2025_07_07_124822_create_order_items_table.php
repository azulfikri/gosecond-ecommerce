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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('product_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('product_name')->comment('Snapshot product name at order time');
            $table->foreignId('size_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');
            $table->string('size_name')->nullable()->comment('Snapshot size name');
            $table->integer('quantity')->default(1)->comment('Quantity of the product ordered');
            $table->decimal('unit_price', 15, 2)->comment('Price per item at order time');
            $table->decimal('total_price', 15, 2)->comment('unit price * quantity at order time');
            $table->foreignId('discount_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
