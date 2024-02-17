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
        Schema::table('users', function (Blueprint $table) {
            $table->double('main_account_balance')->default(0);
            $table->double('secondary_account_balance')->default(0);
            $table->double('referral_account_balance')->default(0);
            $table->boolean('verification_status')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('main_account_balance');
            $table->dropColumn('secondary_account_balance');
            $table->dropColumn('referral_account_balance');
            $table->dropColumn('verification_status');
        });
    }
};
