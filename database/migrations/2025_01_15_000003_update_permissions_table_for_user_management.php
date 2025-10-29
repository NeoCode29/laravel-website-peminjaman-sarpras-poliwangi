<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePermissionsTableForUserManagement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            // Tambah kolom untuk permission management sesuai PRD
            $table->string('display_name')->nullable()->after('name');
            $table->text('description')->nullable()->after('display_name');
            $table->string('category')->nullable()->after('description');
            $table->boolean('is_active')->default(true)->after('category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'description', 'category', 'is_active']);
        });
    }
}
