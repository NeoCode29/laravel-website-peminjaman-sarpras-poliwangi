<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaranaApproversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sarana_approvers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sarana_id');
            $table->unsignedBigInteger('approver_id');
            $table->integer('approval_level')->default(1)->comment('Level hierarki approval (1=primary, 2=secondary, dst)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('sarana_id')->references('id')->on('sarana')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index(['sarana_id']);
            $table->index(['approver_id']);
            $table->index(['approval_level']);
            $table->index(['is_active']);
            $table->unique(['sarana_id', 'approver_id', 'approval_level'], 'sarana_approvers_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sarana_approvers');
    }
}
