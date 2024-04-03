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
        Schema::table('user_package', function (Blueprint $table) {
            $table->bigInteger('total_deposit_app')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('user_package', function (Blueprint $table) {
            $table->integer('total_deposit_app')->change(); // Revert the change if needed
        });
    }
};
