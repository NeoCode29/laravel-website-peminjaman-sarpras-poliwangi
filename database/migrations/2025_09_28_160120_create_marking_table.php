<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarkingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ukm_id')->nullable()->comment('UKM penyelenggara (opsional)');
            $table->unsignedBigInteger('prasarana_id')->nullable()->comment('Prasarana yang di-marking');
            $table->string('lokasi_custom', 255)->nullable()->comment('Lokasi custom jika tidak ada prasarana');
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->integer('jumlah_peserta')->nullable();
            $table->timestamp('expires_at')->comment('Waktu kadaluarsa marking');
            $table->timestamp('planned_submit_by')->nullable()->comment('Rencana submit pengajuan sebelum marking expired');
            $table->enum('status', ['active', 'expired', 'converted', 'cancelled'])->default('active');
            $table->string('event_name', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ukm_id')->references('id')->on('ukm')->onDelete('set null');
            $table->foreign('prasarana_id')->references('id')->on('prasarana')->onDelete('set null');
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['ukm_id']);
            $table->index(['prasarana_id']);
            $table->index(['start_datetime', 'end_datetime']);
            $table->index(['expires_at']);
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marking');
    }
}
