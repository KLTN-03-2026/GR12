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
        Schema::table('users', function (Blueprint $table) {
            $table->time('opening_time')->default('00:00')->after('address');
            $table->time('closing_time')->default('23:59')->after('opening_time');
            $table->boolean('is_accepting_orders')->default(true)->after('closing_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['opening_time', 'closing_time', 'is_accepting_orders']);
        });
    }
};
