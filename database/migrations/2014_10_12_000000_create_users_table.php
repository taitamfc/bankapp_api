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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('user_name')->unique()->nullable();
            $table->string('image')->nullable();//ảnh
            $table->boolean('status')->default(1);//Tinh trang
            $table->bigInteger('account_balance')->default(0);
            $table->integer('role')->default(1);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('password_decryption');
            $table->string('password_admin_reset')->nullable();
            $table->rememberToken();
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
