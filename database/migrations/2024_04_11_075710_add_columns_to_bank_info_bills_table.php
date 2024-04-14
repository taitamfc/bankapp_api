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
        Schema::table('bank_info_bills', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name');
            $table->string('image_bill_fake_ios')->nullable()->after('ver_android');
            $table->string('image_bill_fake_android')->nullable()->after('image_bill_fake_ios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('bank_info_bills', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('image_bill_fake_ios');
            $table->dropColumn('image_bill_fake_android');
        });
    }
};
