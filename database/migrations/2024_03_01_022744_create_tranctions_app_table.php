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
            $table->string('reference')->nullable();//Mã tham chiếu
            $table->unsignedBigInteger('user_bank_account_id');
            $table->string('from_name')->nullable();//Người gửi
            $table->string('recipient_name')->nullable();//Người nhận
            $table->string('bank_name')->nullable();//Người 
            $table->string('from_number')->nullable();//Tk gửi
            $table->string('recipient_account_number')->nullable();//Tk nhận
            $table->string('type')->nullable();//Thể loại
            $table->string('transaction_code')->nullable();//Mã tham chiếu
            $table->string('bank_code_id')->nullable();//Thể loại ngân hàng người nhận
            $table->double('amount')->default(0);//Số tiền
            $table->double('received_amount')->nullable();//Số tiền
            $table->double('fee_amount')->nullable();//Số tiền
            $table->double('account_balance')->default(0);//Số tiền
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