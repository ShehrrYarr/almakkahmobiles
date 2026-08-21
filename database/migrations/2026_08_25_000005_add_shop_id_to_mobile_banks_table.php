<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShopIdToMobileBanksTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('mobile_banks') || Schema::hasColumn('mobile_banks', 'shop_id')) {
            return;
        }

        Schema::table('mobile_banks', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_id')->nullable()->after('id');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('mobile_banks', 'shop_id')) {
            return;
        }

        Schema::table('mobile_banks', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropColumn('shop_id');
        });
    }
}
