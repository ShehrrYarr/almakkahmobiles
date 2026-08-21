<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShopIdToUsersTable extends Migration
{
    /**
     * shop_id replaces has_mobile_access as the mechanism for granting
     * Mobile-section access: null = no Mobile shop assignment, a value =
     * that user is a salesman at that specific shop. Admins bypass this
     * entirely (isAdmin() can enter any shop). Every existing user gets
     * shop_id = null, so nobody's access changes as a side effect.
     */
    public function up()
    {
        if (Schema::hasColumn('users', 'shop_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_id')->nullable()->after('has_accessory_access');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('set null');
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('users', 'shop_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropColumn('shop_id');
        });
    }
}
