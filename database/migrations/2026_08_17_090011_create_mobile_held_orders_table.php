<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileHeldOrdersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('mobile_held_orders')) {
            return;
        }

        Schema::create('mobile_held_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('mobile_vendor_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_mobile')->nullable();
            $table->text('comment')->nullable();
            $table->json('cart_items');
            $table->dateTime('held_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('mobile_vendor_id')->references('id')->on('mobile_vendors')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_held_orders');
    }
}
