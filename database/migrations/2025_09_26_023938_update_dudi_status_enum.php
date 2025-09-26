<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing data first
        DB::table('dudi')->where('status', 'nonaktif')->update(['status' => 'tidak_aktif']);
        DB::table('dudi')->where('status', 'pending')->update(['status' => 'aktif']);
        
        // Modify the enum column
        DB::statement("ALTER TABLE dudi MODIFY COLUMN status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the enum column
        DB::statement("ALTER TABLE dudi MODIFY COLUMN status ENUM('aktif', 'nonaktif', 'pending') DEFAULT 'pending'");
        
        // Revert data
        DB::table('dudi')->where('status', 'tidak_aktif')->update(['status' => 'nonaktif']);
    }
};
