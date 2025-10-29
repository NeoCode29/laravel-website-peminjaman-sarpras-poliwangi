<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveIconFromKategoriTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kategori_sarana', function (Blueprint $table) {
            $table->dropColumn('icon');
        });

        Schema::table('kategori_prasarana', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kategori_sarana', function (Blueprint $table) {
            $table->string('icon', 255)->nullable()->after('description');
        });

        Schema::table('kategori_prasarana', function (Blueprint $table) {
            $table->string('icon', 255)->nullable()->after('description');
        });
    }
}
