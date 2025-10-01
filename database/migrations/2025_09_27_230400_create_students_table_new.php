<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTableNew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('nim')->unique();
            $table->year('angkatan')->nullable();
            $table->unsignedBigInteger('jurusan_id')->nullable();
            $table->unsignedBigInteger('prodi_id')->nullable();
            $table->tinyInteger('semester')->nullable();
            $table->enum('status_mahasiswa', ['aktif', 'cuti', 'dropout', 'lulus'])->default('aktif');
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('jurusan_id')->references('id')->on('jurusan')->onDelete('set null');
            $table->foreign('prodi_id')->references('id')->on('prodi')->onDelete('set null');
            
            // Indexes
            $table->unique('user_id');
            $table->index('angkatan');
            $table->index('jurusan_id');
            $table->index('prodi_id');
            $table->index('status_mahasiswa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
}