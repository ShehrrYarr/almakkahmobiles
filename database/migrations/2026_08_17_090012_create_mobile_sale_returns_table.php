<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileSaleReturnsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('mobile_sale_returns')) {
            return;
        }

        Schema::create('mobile_sale_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mobile_sale_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('mobile_sale_id')->references('id')->on('mobile_sales')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_sale_returns');
    }
}
