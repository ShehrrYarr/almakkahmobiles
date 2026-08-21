<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileSaleReturnItemsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('mobile_sale_return_items')) {
            return;
        }

        Schema::create('mobile_sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mobile_sale_return_id');
            $table->unsignedBigInteger('mobile_sale_item_id');
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('mobile_sale_return_id')->references('id')->on('mobile_sale_returns')->onDelete('cascade');
            $table->foreign('mobile_sale_item_id')->references('id')->on('mobile_sale_items')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_sale_return_items');
    }
}
