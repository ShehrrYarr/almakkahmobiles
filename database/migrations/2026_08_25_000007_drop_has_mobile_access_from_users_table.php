<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropHasMobileAccessFromUsersTable extends Migration
{
    /**
     * Superseded by shop_id (see add_shop_id_to_users_table). Note:
     * has_accessory_access is untouched — it's still the real gate for the
     * existing Accessory section and isn't affected by this change at all.
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'has_mobile_access')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('has_mobile_access');
        });
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'has_mobile_access')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_mobile_access')->default(false)->after('has_accessory_access');
        });
    }
}
