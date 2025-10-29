<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarkingItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marking_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marking_id');
            $table->unsignedBigInteger('sarana_id');
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('marking_id')->references('id')->on('marking')->onDelete('cascade');
            $table->foreign('sarana_id')->references('id')->on('sarana')->onDelete('cascade');
            
            // Indexes
            $table->index(['marking_id']);
            $table->index(['sarana_id']);
            $table->unique(['marking_id', 'sarana_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marking_items');
    }
}
