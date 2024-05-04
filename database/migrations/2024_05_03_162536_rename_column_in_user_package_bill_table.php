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
            $table->renameColumn('billpackage_id', 'type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('user_package_bill', function (Blueprint $table) {
            $table->renameColumn('type', 'billpackage_id');
        });
    }
};
