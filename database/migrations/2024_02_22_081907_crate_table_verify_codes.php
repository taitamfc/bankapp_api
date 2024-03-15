<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verify_codes', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('code');
            $table->string('email')->nullable();
            $table->foreignId('user_id')->constrained('users');//User ID
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verify_codes');
    }
};
