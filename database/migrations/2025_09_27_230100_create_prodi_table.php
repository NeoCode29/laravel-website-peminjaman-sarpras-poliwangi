<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prodi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_prodi')->unique();
            $table->unsignedBigInteger('jurusan_id');
            $table->enum('jenjang', ['D3', 'D4', 'S1', 'S2', 'S3']);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('jurusan_id')->references('id')->on('jurusan')->onDelete('cascade');
            
            // Indexes
            $table->index('nama_prodi');
            $table->index('jurusan_id');
            $table->index('jenjang');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prodi');
    }
}
