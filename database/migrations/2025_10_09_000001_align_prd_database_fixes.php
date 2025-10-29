<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Optimize peminjaman conflict lookups
        if (Schema::hasTable('peminjaman')) {
            Schema::table('peminjaman', function (Blueprint $table) {
                // Indexes for date range and status filtering
                $table->index(['start_date', 'end_date'], 'peminjaman_start_end_idx');
                $table->index(['status'], 'peminjaman_status_idx');
                // Prasarana conflict fast path
                $table->index(['prasarana_id', 'start_date', 'end_date'], 'peminjaman_prasarana_date_idx');
                $table->index(['user_id'], 'peminjaman_user_idx');
            });
        }

        // Optimize peminjaman items lookup by sarana
        if (Schema::hasTable('peminjaman_items')) {
            Schema::table('peminjaman_items', function (Blueprint $table) {
                $table->index(['sarana_id'], 'peminjaman_items_sarana_idx');
                $table->index(['peminjaman_id'], 'peminjaman_items_peminjaman_idx');
            });
        }

        // Ensure approval workflow filtering is efficient
        if (Schema::hasTable('peminjaman_approval_workflow')) {
            Schema::table('peminjaman_approval_workflow', function (Blueprint $table) {
                $table->index(['peminjaman_id', 'status'], 'paw_peminjaman_status_idx');
                $table->index(['approver_id', 'status'], 'paw_approver_status_idx');
                $table->index(['approval_type', 'sarana_id', 'prasarana_id'], 'paw_type_target_idx');
            });
        }

        // Reinforce uniqueness at application-level for sarana_units via index (if table exists)
        if (Schema::hasTable('sarana_units')) {
            Schema::table('sarana_units', function (Blueprint $table) {
                // Composite unique helps prevent duplicate unit_code per sarana
                // Note: If already exists, Laravel will ignore duplicate name at runtime
                $table->unique(['sarana_id', 'unit_code'], 'sarana_units_unique_per_sarana');
                $table->index(['unit_status'], 'sarana_units_status_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('peminjaman')) {
            Schema::table('peminjaman', function (Blueprint $table) {
                $table->dropIndex('peminjaman_start_end_idx');
                $table->dropIndex('peminjaman_status_idx');
                $table->dropIndex('peminjaman_prasarana_date_idx');
                $table->dropIndex('peminjaman_user_idx');
            });
        }
        if (Schema::hasTable('peminjaman_items')) {
            Schema::table('peminjaman_items', function (Blueprint $table) {
                $table->dropIndex('peminjaman_items_sarana_idx');
                $table->dropIndex('peminjaman_items_peminjaman_idx');
            });
        }
        if (Schema::hasTable('peminjaman_approval_workflow')) {
            Schema::table('peminjaman_approval_workflow', function (Blueprint $table) {
                $table->dropIndex('paw_peminjaman_status_idx');
                $table->dropIndex('paw_approver_status_idx');
                $table->dropIndex('paw_type_target_idx');
            });
        }
        if (Schema::hasTable('sarana_units')) {
            Schema::table('sarana_units', function (Blueprint $table) {
                $table->dropUnique('sarana_units_unique_per_sarana');
                $table->dropIndex('sarana_units_status_idx');
            });
        }
    }
};



