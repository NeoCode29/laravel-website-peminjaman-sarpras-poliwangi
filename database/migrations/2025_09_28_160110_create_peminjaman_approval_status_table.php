<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeminjamanApprovalStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('peminjaman_approval_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('peminjaman_id');
            $table->enum('overall_status', ['pending', 'approved', 'partially_approved', 'rejected'])->default('pending');
            $table->enum('global_approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('global_approved_by')->nullable();
            $table->timestamp('global_approved_at')->nullable();
            $table->unsignedBigInteger('global_rejected_by')->nullable();
            $table->timestamp('global_rejected_at')->nullable();
            $table->text('global_rejection_reason')->nullable();
            $table->json('specific_approval_summary')->nullable()->comment('Summary approval per sarpras: {sarana_id: status, prasarana_id: status}');
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('peminjaman_id')->references('id')->on('peminjaman')->onDelete('cascade');
            $table->foreign('global_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('global_rejected_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['peminjaman_id']);
            $table->index(['overall_status']);
            $table->index(['global_approval_status']);
            $table->index(['global_approved_by']);
            $table->index(['global_rejected_by']);
            $table->unique(['peminjaman_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('peminjaman_approval_status');
    }
}
