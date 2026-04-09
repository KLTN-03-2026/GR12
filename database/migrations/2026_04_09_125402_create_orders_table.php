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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_code')->unique(); // Mã đơn hàng (VD: FXP-123456)
            $table->string('address');
            $table->string('phone');
            $table->text('note')->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('shipping_fee', 15, 2);
            $table->decimal('total', 15, 2);
            $table->string('payment_method');
            $table->string('status')->default('pending'); // pending, processing, shipping, completed, cancelled
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
