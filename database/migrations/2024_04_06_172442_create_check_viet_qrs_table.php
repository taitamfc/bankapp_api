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
        Schema::create('check_viet_qrs', function (Blueprint $table) {
            $table->id();
            $table->integer('bin'); // Cột bin kiểu integer
            $table->integer('bank_acount'); // Cột bank_acount kiểu integer
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('check_viet_qrs');
    }
};
