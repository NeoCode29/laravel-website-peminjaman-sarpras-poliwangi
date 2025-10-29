<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeminjamanApprovalWorkflowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('peminjaman_approval_workflow', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('peminjaman_id');
            $table->unsignedBigInteger('approver_id');
            $table->enum('approval_type', ['global', 'sarana', 'prasarana'])->comment('Jenis approval: global, sarana spesifik, atau prasarana spesifik');
            $table->unsignedBigInteger('sarana_id')->nullable()->comment('ID sarana jika approval_type = sarana');
            $table->unsignedBigInteger('prasarana_id')->nullable()->comment('ID prasarana jika approval_type = prasarana');
            $table->integer('approval_level')->default(1)->comment('Level hierarki approval');
            $table->enum('status', ['pending', 'approved', 'rejected', 'overridden'])->default('pending');
            $table->text('notes')->nullable()->comment('Catatan approval/rejection');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('overridden_by')->nullable()->comment('User yang melakukan override');
            $table->timestamp('overridden_at')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('peminjaman_id')->references('id')->on('peminjaman')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sarana_id')->references('id')->on('sarana')->onDelete('cascade');
            $table->foreign('prasarana_id')->references('id')->on('prasarana')->onDelete('cascade');
            $table->foreign('overridden_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['peminjaman_id']);
            $table->index(['approver_id']);
            $table->index(['approval_type']);
            $table->index(['sarana_id']);
            $table->index(['prasarana_id']);
            $table->index(['status']);
            $table->index(['approval_level']);
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
        Schema::dropIfExists('peminjaman_approval_workflow');
    }
}
