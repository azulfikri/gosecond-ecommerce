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
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 11, 2);
            $table->integer('stock')->default(0);
            $table->integer('weight')->default(0);
            $table->enum('condition', ['new', 'used'])->default('used');
            $table->foreignId('brand_id')->constrained()->OnDelete('cascade');
            $table->foreignId('category_id')->constrained()->OnDelete('cascade');
            $table->timestamps();
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
