<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RestructureMobileSalesTable extends Migration
{
    /**
     * No more vendor-credit sales — every mobile sale is now a plain
     * walk-in customer sale, scoped to the shop it was made in.
     */
    public function up()
    {
        if (!Schema::hasTable('mobile_sales') || !Schema::hasColumn('mobile_sales', 'mobile_vendor_id')) {
            return;
        }

        Schema::table('mobile_sales', function (Blueprint $table) {
            $table->dropForeign(['mobile_vendor_id']);
            $table->dropColumn('mobile_vendor_id');
            $table->unsignedBigInteger('shop_id')->nullable()->after('id');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
        });
    }

    public function down()
    {
        // Forward-only.
    }
}
