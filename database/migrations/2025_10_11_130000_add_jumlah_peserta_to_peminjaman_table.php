<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('peminjaman') && !Schema::hasColumn('peminjaman', 'jumlah_peserta')) {
            Schema::table('peminjaman', function (Blueprint $table) {
                $table->unsignedInteger('jumlah_peserta')->nullable()->after('lokasi_custom');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('peminjaman') && Schema::hasColumn('peminjaman', 'jumlah_peserta')) {
            Schema::table('peminjaman', function (Blueprint $table) {
                $table->dropColumn('jumlah_peserta');
            });
        }
    }
};
