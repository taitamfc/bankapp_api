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
        Schema::table('transactions', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
            $table->bigInteger('received')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('amount')->change(); // Revert the change if needed
            $table->integer('received')->change(); // Revert the change if needed
        });
    }
};
