<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileVendorsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('mobile_vendors')) {
            return;
        }

        Schema::create('mobile_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('office_address')->nullable();
            $table->string('city')->nullable();
            $table->string('CNIC')->nullable();
            $table->string('mobile_no');
            $table->string('picture')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_vendors');
    }
}
