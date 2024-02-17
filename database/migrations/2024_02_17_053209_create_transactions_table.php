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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference');//Mã tham chiếu
            $table->string('type');//Thể loại
            $table->string('type_money')->nullable();//Loại tiền
            $table->double('amount')->default(0);//Số tiền
            $table->double('received')->default(0);//Thực nhận
            $table->text('note')->nullable();//Ghi chú
            $table->foreignId('user_id')->constrained('users');//User ID
            $table->boolean('status')->default(0);//Tinh trang
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
