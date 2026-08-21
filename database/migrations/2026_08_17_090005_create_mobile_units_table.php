<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileUnitsTable extends Migration
{
    /**
     * One row = one physical phone (serialized by IMEI), unlike
     * accessory_batches where one row represents a quantity of identical
     * stock. Purchasing "a batch" of phones from a vendor creates one
     * mobile_units row per phone, each with its own IMEI/condition/images.
     */
    public function up()
    {
        if (Schema::hasTable('mobile_units')) {
            return;
        }

        Schema::create('mobile_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mobile_id');
            $table->unsignedBigInteger('mobile_vendor_id');
            $table->string('imei')->unique();
            $table->string('storage')->nullable();
            $table->enum('pta_status', ['PTA', 'Non PTA', 'JV']);
            $table->string('battery')->nullable();
            $table->integer('battery_cycle')->nullable();
            $table->decimal('purchase_price', 12, 2);
            $table->decimal('selling_price', 12, 2);
            $table->date('purchase_date');
            $table->text('description')->nullable();
            $table->enum('status', ['in_stock', 'sold'])->default('in_stock');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('mobile_id')->references('id')->on('mobiles')->onDelete('cascade');
            $table->foreign('mobile_vendor_id')->references('id')->on('mobile_vendors')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_units');
    }
}
