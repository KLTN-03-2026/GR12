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
    $table->foreignId('user_id')->constrained();
    $table->string('order_number')->unique(); // VD: FX-20240403-XXXX
    $table->decimal('total_amount', 15, 2);
    $table->string('payment_method')->default('cod'); // cod, vnpay, momo
    $table->enum('status', ['pending', 'confirmed', 'processing', 'shipping', 'delivered', 'cancelled'])
          ->default('pending');
    $table->text('shipping_address');
    $table->text('note')->nullable();
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
