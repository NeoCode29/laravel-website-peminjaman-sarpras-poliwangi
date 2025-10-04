<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeminjamanItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('peminjaman_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('peminjaman_id');
            $table->unsignedBigInteger('sarana_id');
            $table->unsignedInteger('qty_requested')->default(0);
            $table->unsignedInteger('qty_approved')->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('peminjaman_id')->references('id')->on('peminjaman')->onDelete('cascade');
            $table->foreign('sarana_id')->references('id')->on('sarana')->onDelete('cascade');

            // Indexes
            $table->index('peminjaman_id');
            $table->index('sarana_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('peminjaman_items');
    }
}



