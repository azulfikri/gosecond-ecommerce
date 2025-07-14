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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable()->comment('Unique discount for code');
            $table->string('name')->nullable()->comment('Name of the discount');
            $table->text('description')->nullable()->comment('Description of the discount');
            $table->enum('type', ['PERCENTAGE', 'FIXED'])->default('PERCENTAGE')->comment('Type of discount: percentage or fixed amount');
            $table->decimal('value', 10, 2)->default(0)->comment('Value of the discount');
            $table->decimal('minimum_purchase', 10, 2)->default(0)->comment('Minimum order value to apply the discount');
            $table->integer('usage_limit')->nullable()->comment('Maximum number of times the discount can be used');
            $table->integer('used_count')->default(0)->comment('Number of times the discount has been used');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null')->comment('Optional product associated with the discount');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null')->comment('Optional category associated with the discount');
            $table->boolean('is_active')->default(true)->comment('Whether the discount is currently active');
            $table->dateTime('start_date')->nullable()->comment('Start date for the discount');
            $table->dateTime('end_date')->nullable()->comment('End date for the discount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
