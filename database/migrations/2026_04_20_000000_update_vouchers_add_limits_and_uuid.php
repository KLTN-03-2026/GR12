<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->unsignedInteger('max_uses')->default(0)->after('expires_at');
            $table->unsignedInteger('used_count')->default(0)->after('max_uses');
            $table->decimal('minimum_product_price', 15, 2)->nullable()->after('used_count');
        });

        $vouchers = DB::table('vouchers')->select('id')->get();
        foreach ($vouchers as $voucher) {
            DB::table('vouchers')
                ->where('id', $voucher->id)
                ->update(['uuid' => (string) Str::uuid()]);
        }
    }

    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'max_uses', 'used_count', 'minimum_product_price']);
        });
    }
};
