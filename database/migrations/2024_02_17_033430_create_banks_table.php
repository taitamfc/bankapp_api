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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');//Ten ngan hang
            $table->boolean('status')->default(0);//Tinh trang
            $table->string('image')->nullable();//ảnh
            $table->double('opening_fee')->default(0); // Giá mở ngân hàng
            $table->double('account_opening_fee')->default(100000); // Giá mở tài khoản
            $table->double('max_accounts')->default(10); // Mở tối đa
            $table->double('app_deposit_fee')->default(20000); // Giá nạp tiền vào TK APP
            $table->float('app_deposit_fee_percentage')->default(0.1); // Giá nạp tiền vào TK APP (phần trăm)
            $table->double('app_transfer_fee')->default(88000); // Giá chuyển tiền trong APP
            $table->float('app_transfer_fee_percentage')->default(0.1); // Giá chuyển tiền trong APP (phần trăm)
            $table->double('auto_check_account_fee')->default(2000); // Giá kiểm tra STK tự động
            $table->text('important_note')->nullable(); // Lưu ý quan trọng
            $table->string('android_version')->nullable(); // Phiên bản Android
            $table->string('ios_version')->nullable(); // Phiên bản IOS
            $table->string('android_download_link')->nullable(); // Link tải Android
            $table->string('ios_download_link')->nullable(); // Link tải IOS
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
