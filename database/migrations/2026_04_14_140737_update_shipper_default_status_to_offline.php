<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all shippers with 'available' status to 'offline'
        DB::table('shippers')->where('status', 'available')->update(['status' => 'offline']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to 'available' status
        DB::table('shippers')->where('status', 'offline')->update(['status' => 'available']);
    }
};
