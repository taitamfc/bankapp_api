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
        Schema::table('user_package_bill', function (Blueprint $table) {
            $table->integer('duration_vip_bill')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('user_package_bill', function (Blueprint $table) {
            $table->dropColumn('duration_vip_bill');
        });
    }
};
