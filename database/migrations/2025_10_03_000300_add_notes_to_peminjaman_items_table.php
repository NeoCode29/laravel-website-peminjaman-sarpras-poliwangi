<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotesToPeminjamanItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('peminjaman_items', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('qty_approved')->comment('Catatan untuk item peminjaman');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('peminjaman_items', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
}
