<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prasarana_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prasarana_id');
            $table->string('image_url');
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('prasarana_id')->references('id')->on('prasarana')->onDelete('cascade');

            $table->index(['prasarana_id', 'sort_order']);
            $table->index(['prasarana_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prasarana_images');
    }
};



