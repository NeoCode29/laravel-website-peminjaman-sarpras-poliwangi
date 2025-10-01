<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaranaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sarana', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->unsignedBigInteger('kategori_id');
            $table->enum('type', ['serialized', 'pooled']);
            $table->unsignedInteger('jumlah_total')->default(0);
            $table->text('description')->nullable();
            $table->string('image_url', 255)->nullable();
            $table->string('lokasi', 150)->nullable();
            $table->unsignedInteger('jumlah_tersedia')->default(0);
            $table->unsignedInteger('jumlah_rusak')->default(0);
            $table->unsignedInteger('jumlah_maintenance')->default(0);
            $table->unsignedInteger('jumlah_hilang')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('kategori_id')->references('id')->on('kategori_sarana')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('kategori_id');
            $table->index('type');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sarana');
    }
}
