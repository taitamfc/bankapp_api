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
        Schema::create('user_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');//Ten ngan hang
            $table->string('image')->nullable();//ảnh
            $table->string('phone')->unique()->nullable();// Số điện thoại
            $table->string('bank_number')->nullable();
            $table->string('password_level_two');
            $table->string('type')->nullable();//Thể loại
            $table->integer('account_balance')->default(0);
            $table->string('bank_username')->nullable();
            $table->boolean('status')->default(0);//Tinh trang
            $table->foreignId('user_id')->constrained('users');//User ID
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bank_accounts');
    }
};
