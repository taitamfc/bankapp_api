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
        Schema::create('user_package', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('type_package')->nullable();
            $table->integer('total_create_account')->nullable()->default(0);
            $table->integer('total_edit_account')->nullable()->default(0);
            $table->integer('total_transfer_app')->nullable()->default(0);
            $table->integer('total_deposit_app')->nullable()->default(0);
            $table->date('start_day')->nullable();
            $table->date('end_day')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_package');
    }
};
