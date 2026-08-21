<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RestructureMobileHeldOrdersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('mobile_held_orders') || !Schema::hasColumn('mobile_held_orders', 'mobile_vendor_id')) {
            return;
        }

        Schema::table('mobile_held_orders', function (Blueprint $table) {
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
