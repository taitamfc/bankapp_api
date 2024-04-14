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
        Schema::rename('banh_info_bills', 'bank_info_bills');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::rename('bank_info_bills', 'banh_info_bills');
    }
};
