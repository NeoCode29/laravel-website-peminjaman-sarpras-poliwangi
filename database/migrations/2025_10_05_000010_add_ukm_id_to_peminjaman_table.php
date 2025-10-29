<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUkmIdToPeminjamanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            if (!Schema::hasColumn('peminjaman', 'ukm_id')) {
                $table->unsignedBigInteger('ukm_id')->nullable()->after('user_id')->comment('UKM penyelenggara jika peminjam adalah mahasiswa');
                $table->foreign('ukm_id')->references('id')->on('ukm')->onDelete('set null');
                $table->index(['ukm_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            if (Schema::hasColumn('peminjaman', 'ukm_id')) {
                $table->dropForeign(['ukm_id']);
                $table->dropIndex(['ukm_id']);
                $table->dropColumn('ukm_id');
            }
        });
    }
}



