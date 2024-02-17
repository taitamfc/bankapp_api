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
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Ngân hàng
            $table->boolean('status')->default(0);//Tinh trang
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
        Schema::dropIfExists('apps');
    }
};
