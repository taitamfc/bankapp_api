<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable();
            // Thay 'package_id' bằng tên cột mà bạn muốn sử dụng

            $table->foreign('package_id')->references('id')->on('packages');
            // Thay 'packages' bằng tên bảng liên kết và 'id' bằng tên cột khóa chính của bảng liên kết
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
        });
    }
};
