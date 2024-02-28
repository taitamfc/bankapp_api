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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->unique()->after('email');// Số điện thoại
            $table->string('password_confirmation')->after('password');// Mật khẩu cấp 2
            $table->string('referral_code')->after('password_confirmation')->nullable();// Mã giới thiệu
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropColumn('password_confirmation');
            $table->dropColumn('referral_code');
        });
    }
};
