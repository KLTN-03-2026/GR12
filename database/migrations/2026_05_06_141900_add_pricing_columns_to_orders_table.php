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
            $table->decimal('service_fee', 15, 2)->default(3000)->after('shipping_fee');
            $table->decimal('packaging_fee', 15, 2)->default(0)->after('service_fee');
            
            $table->decimal('restaurant_commission_rate', 5, 4)->default(0.25)->after('total');
            $table->decimal('restaurant_revenue', 15, 2)->default(0)->after('restaurant_commission_rate');
            $table->decimal('restaurant_tax_fee', 15, 2)->default(0)->after('restaurant_revenue');
            
            $table->decimal('shipper_tip', 15, 2)->default(0)->after('shipper_fee');
            $table->decimal('shipper_bonus', 15, 2)->default(0)->after('shipper_tip');
            
            $table->decimal('admin_revenue', 15, 2)->default(0)->after('shipper_bonus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'service_fee',
                'packaging_fee',
                'restaurant_commission_rate',
                'restaurant_revenue',
                'restaurant_tax_fee',
                'shipper_tip',
                'shipper_bonus',
                'admin_revenue'
            ]);
        });
    }
};
