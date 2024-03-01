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
        Schema::create('app_tranctions', function (Blueprint $table) {
            $table->id();
            $table->string('reference');//Mã tham chiếu
            $table->unsignedBigInteger('user_bank_account_id');
            $table->string('from_name')->nullable();//Người gửi
            $table->string('to_name')->nullable();//Người nhận
            $table->string('from_number')->nullable();//Tk gửi
            $table->string('to_number')->nullable();//Tk nhận
            $table->string('type')->nullable();//Thể loại
            $table->double('amount')->default(0);//Số tiền
            $table->double('surplus')->default(0);//Số tiền
            $table->text('note')->nullable();//Ghi chú
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tranctions_app');
    }
};