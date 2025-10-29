<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalApproversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_approvers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approver_id');
            $table->integer('approval_level')->default(1)->comment('Level hierarki approval (1=primary, 2=secondary, dst)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index(['approver_id']);
            $table->index(['approval_level']);
            $table->index(['is_active']);
            $table->unique(['approver_id', 'approval_level']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('global_approvers');
    }
}
