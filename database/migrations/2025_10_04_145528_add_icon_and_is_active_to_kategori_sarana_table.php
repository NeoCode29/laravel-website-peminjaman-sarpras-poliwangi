<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIconAndIsActiveToKategoriSaranaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kategori_sarana', function (Blueprint $table) {
            $table->string('icon', 100)->nullable()->after('description');
            $table->boolean('is_active')->default(true)->after('icon');
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
            $table->dropColumn(['icon', 'is_active']);
        });
    }
}
