<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssignedFieldsToPeminjamanItemUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('peminjaman_item_units', function (Blueprint $table) {
            $table->unsignedBigInteger('peminjaman_item_id')->nullable()->after('unit_id');
            $table->foreign('peminjaman_item_id')->references('id')->on('peminjaman_items')->onDelete('cascade');

            $table->unsignedBigInteger('assigned_by')->nullable()->after('peminjaman_item_id');
            $table->timestamp('assigned_at')->nullable()->after('assigned_by');

            // Foreign key untuk assigned_by ke users table
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('peminjaman_item_units', function (Blueprint $table) {
            $table->dropForeign(['peminjaman_item_id']);
            $table->dropForeign(['assigned_by']);
            $table->dropColumn(['peminjaman_item_id', 'assigned_by', 'assigned_at']);
        });
    }
}
