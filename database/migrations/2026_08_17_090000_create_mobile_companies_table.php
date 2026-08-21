<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileCompaniesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('mobile_companies')) {
            return;
        }

        Schema::create('mobile_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_companies');
    }
}
