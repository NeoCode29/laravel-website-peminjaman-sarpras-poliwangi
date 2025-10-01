<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoginCountToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('login_count')->default(0)->after('last_login_at');
            $table->integer('failed_login_attempts')->default(0)->after('login_count');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->string('blocked_reason')->nullable()->after('locked_until');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['login_count', 'failed_login_attempts', 'locked_until', 'blocked_reason']);
        });
    }
}
