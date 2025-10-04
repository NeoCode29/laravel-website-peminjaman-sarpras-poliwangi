<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeminjamanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('prasarana_id')->nullable();
            $table->string('event_name')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'picked_up', 'returned', 'cancelled'])->default('pending');
            $table->string('surat_path')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('pickup_validated_by')->nullable();
            $table->timestamp('pickup_validated_at')->nullable();
            $table->unsignedBigInteger('return_validated_by')->nullable();
            $table->timestamp('return_validated_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('foto_pickup_path')->nullable();
            $table->string('foto_return_path')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // prasarana_id foreign key will be added later after prasarana table is created
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('pickup_validated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('return_validated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['prasarana_id']);
            $table->index(['status']);
            $table->index(['start_date', 'end_date']);
            $table->index(['approved_by']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('peminjaman');
    }
}
