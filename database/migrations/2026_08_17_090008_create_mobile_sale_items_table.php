<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileSaleItemsTable extends Migration
{
    /**
     * One row per unit sold — no quantity column, since each mobile_unit is
     * exactly one physical phone. A unit can be sold more than once over its
     * lifetime (sale, then return, then resale), so mobile_unit_id isn't
     * globally unique here; "can't sell an already-sold unit" is enforced in
     * the controller by checking mobile_units.status before the sale.
     */
    public function up()
    {
        if (Schema::hasTable('mobile_sale_items')) {
            return;
        }

        Schema::create('mobile_sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mobile_sale_id');
            $table->unsignedBigInteger('mobile_unit_id');
            $table->decimal('price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('mobile_sale_id')->references('id')->on('mobile_sales')->onDelete('cascade');
            $table->foreign('mobile_unit_id')->references('id')->on('mobile_units')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_sale_items');
    }
}
