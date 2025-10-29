<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('Setting key identifier');
            $table->text('value')->nullable()->comment('Setting value');
            $table->text('description')->nullable()->comment('Deskripsi setting');
            $table->string('type', 50)->default('string')->comment('Data type: string, integer, boolean, json');
            $table->boolean('is_editable')->default(true)->comment('Apakah setting bisa diedit dari UI');
            $table->timestamps();
            
            // Indexes
            $table->index(['key']);
            $table->index(['is_editable']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_settings');
    }
}
