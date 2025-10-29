<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('peminjaman_items')) {
            return;
        }

        DB::statement('ALTER TABLE `peminjaman_items` MODIFY `qty_approved` INT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('peminjaman_items')) {
            return;
        }

        DB::statement('ALTER TABLE `peminjaman_items` MODIFY `qty_approved` INT UNSIGNED NOT NULL DEFAULT 0');
    }
};
