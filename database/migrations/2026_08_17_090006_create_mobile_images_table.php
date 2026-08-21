<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileImagesTable extends Migration
{
    /**
     * Up to 5 images per unit — the cap is enforced in the controller, not
     * the schema.
     */
    public function up()
    {
        if (Schema::hasTable('mobile_images')) {
            return;
        }

        Schema::create('mobile_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mobile_unit_id');
            $table->string('path');
            $table->timestamps();

            $table->foreign('mobile_unit_id')->references('id')->on('mobile_units')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_images');
    }
}
