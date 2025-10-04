<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaranaUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sarana_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sarana_id');
            $table->string('unit_code', 80);
            $table->enum('unit_status', ['tersedia', 'rusak', 'maintenance', 'hilang'])->default('tersedia');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('sarana_id')->references('id')->on('sarana')->onDelete('cascade');

            // Indexes
            $table->unique(['sarana_id', 'unit_code']);
            $table->index('sarana_id');
            $table->index('unit_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sarana_units');
    }
}
