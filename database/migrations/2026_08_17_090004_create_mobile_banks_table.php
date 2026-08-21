<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileBanksTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('mobile_banks')) {
            return;
        }

        Schema::create('mobile_banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('account_no')->nullable();
            $table->string('branch')->nullable();
            $table->string('iban')->nullable();
            $table->string('swift')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_banks');
    }
}
