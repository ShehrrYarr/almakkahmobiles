<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSectionAccessToUsersTable extends Migration
{
    /**
     * has_accessory_access defaults true so every EXISTING user keeps
     * exactly today's access (accessory pages currently have no section
     * gate at all). has_mobile_access defaults false — nobody gets the new
     * Mobile section automatically; the admin grants it explicitly.
     */
    public function up()
    {
        if (Schema::hasColumn('users', 'has_accessory_access')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_accessory_access')->default(true)->after('role');
            $table->boolean('has_mobile_access')->default(false)->after('has_accessory_access');
        });

        // Explicit backfill (belt-and-suspenders alongside the column default)
        // so every pre-existing row is unambiguously accessory=true, mobile=false.
        DB::table('users')->update([
            'has_accessory_access' => true,
            'has_mobile_access'    => false,
        ]);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['has_accessory_access', 'has_mobile_access']);
        });
    }
}
