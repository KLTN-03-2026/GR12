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
            // 1. Liên kết với Quán ăn (Người bán)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // 2. Liên kết với Danh mục (Cơm, Bún, Nước...)
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('discount_price', 15, 2)->nullable();
            $table->string('image')->nullable();
            
            // Với FoodXpress, stock_quantity có thể hiểu là số lượng suất ăn có sẵn trong ngày
            $table->integer('stock_quantity')->default(0); 
            $table->boolean('is_available')->default(true);
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
