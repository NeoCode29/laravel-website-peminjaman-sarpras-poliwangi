<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrasaranaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prasarana', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('kategori_id');
            $table->text('description')->nullable();
            $table->enum('status', ['tersedia', 'rusak', 'maintenance'])->default('tersedia');
            $table->integer('kapasitas')->nullable();
            $table->string('lokasi')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('kategori_id')->references('id')->on('kategori_prasarana')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index(['kategori_id']);
            $table->index(['status']);
            $table->index(['name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prasarana');
    }
}
