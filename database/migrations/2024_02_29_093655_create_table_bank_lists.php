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
        Schema::create('bank_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');//Ten ngan hang
            $table->string('image')->nullable();//ảnh
            $table->string('bank_number')->nullable();
            $table->string('bank_username')->nullable();
            $table->boolean('status')->default(0);//Tinh trang
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_lists');
    }
};
