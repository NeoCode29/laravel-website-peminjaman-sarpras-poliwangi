<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->enum('user_type', ['mahasiswa', 'staff'])->default('mahasiswa');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->boolean('profile_completed')->default(false);
            $table->timestamp('profile_completed_at')->nullable();
            $table->timestamp('blocked_until')->nullable();
            $table->string('sso_id')->nullable()->unique();
            $table->string('sso_provider')->default('poliwangi');
            $table->json('sso_data')->nullable();
            $table->timestamp('last_sso_login')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
            
            // Indexes
            $table->index(['username', 'email']);
            $table->index(['user_type', 'status']);
            $table->index('sso_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
