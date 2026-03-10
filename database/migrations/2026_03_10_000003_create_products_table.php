<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 50)->unique();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->enum('stock_status', ['in_stock', 'out_of_stock'])->default('in_stock');
            $table->boolean('is_active')->default(true);
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->timestamps();

            $table->index('slug', 'idx_products_slug');
            $table->index('code', 'idx_products_code');
            $table->index('category_id', 'idx_products_category');
            $table->index(['is_active', 'stock_status'], 'idx_products_active_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
