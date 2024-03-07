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
        Schema::create('owner_banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');//Ten ngan hang
            $table->boolean('status')->default(0);//Tinh trang
            $table->string('image')->nullable();//ảnh
            $table->string('bin')->nullable();//ảnh
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_banks');
    }
};
