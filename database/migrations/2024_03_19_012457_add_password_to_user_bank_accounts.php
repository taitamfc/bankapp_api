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
        Schema::table('user_bank_accounts', function (Blueprint $table) {
            $table->string('password')->nullable();
            $table->string('password_decryption')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('user_bank_accounts', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->dropColumn('password_decryption');
        });
    }
};
