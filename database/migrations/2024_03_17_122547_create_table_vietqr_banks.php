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
        Schema::create('vietqr_banks', function (Blueprint $table) {
            $table->id();
            $table->string('bin')->nullable();//Người nhận
            $table->string('code')->nullable();//Người 
            $table->string('logo')->nullable();//Tk gửi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vietqr_banks');
    }
};
