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
        Schema::table('app_tranctions', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
            $table->bigInteger('received_amount')->change();
            $table->bigInteger('account_balance')->change();
            $table->bigInteger('fee_amount')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('app_tranctions', function (Blueprint $table) {
            $table->integer('amount')->change(); // Revert the change if needed
            $table->integer('received_amount')->change(); // Revert the change if needed
            $table->integer('account_balance')->change(); // Revert the change if needed
            $table->integer('fee_amount')->change(); // Revert the change if needed
        });
    }
};
