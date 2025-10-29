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
        Schema::table('peminjaman_item_units', function (Blueprint $table) {
            if (Schema::hasColumn('peminjaman_item_units', 'peminjaman_id') && Schema::hasColumn('peminjaman_item_units', 'unit_id')) {
                $table->dropUnique('peminjaman_item_units_peminjaman_id_unit_id_unique');
            }

            $table->string('status', 20)->default('active')->after('assigned_at');
            $table->unsignedBigInteger('released_by')->nullable()->after('status');
            $table->timestamp('released_at')->nullable()->after('released_by');

            $table->foreign('released_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['peminjaman_id', 'unit_id', 'status'], 'peminjaman_item_units_unique_assignment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman_item_units', function (Blueprint $table) {
            $table->dropUnique('peminjaman_item_units_unique_assignment_status');
            $table->dropForeign(['released_by']);
            $table->dropColumn(['status', 'released_by', 'released_at']);
            $table->unique(['peminjaman_id', 'unit_id']);
        });
    }
};
