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
        Schema::table('orders', function (Blueprint $table) {
            // Thêm các timestamp để track giai đoạn của đơn hàng
            $table->timestamp('confirmed_at')->nullable()->after('status'); // Khi quán xác nhận
            $table->timestamp('picked_up_at')->nullable()->after('confirmed_at'); // Khi shipper lấy hàng
            $table->timestamp('delivering_at')->nullable()->after('picked_up_at'); // Khi bắt đầu giao
            $table->timestamp('completed_at')->nullable()->after('delivering_at'); // Khi hoàn tất
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['confirmed_at', 'picked_up_at', 'delivering_at', 'completed_at']);
        });
    }
};
