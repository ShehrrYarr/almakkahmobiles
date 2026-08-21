<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobilesTable extends Migration
{
    /**
     * Mobile "catalog" entry — mirrors the Accessory model (name + company +
     * group). Individual purchased phones (IMEI, storage, PTA, battery,
     * images) live in mobile_units, one catalog entry hasMany units.
     */
    public function up()
    {
        if (Schema::hasTable('mobiles')) {
            return;
        }

        Schema::create('mobiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('min_qty')->nullable();
            $table->unsignedBigInteger('mobile_company_id');
            $table->unsignedBigInteger('mobile_group_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('mobile_company_id')->references('id')->on('mobile_companies')->onDelete('cascade');
            $table->foreign('mobile_group_id')->references('id')->on('mobile_groups')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobiles');
    }
}
