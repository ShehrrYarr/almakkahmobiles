<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileGroupsTable extends Migration
{
    /**
     * "Group" for mobiles represents condition (New / Used / Refurbished, etc.)
     * — a growable lookup table, not a fixed enum, so the shop can add more
     * condition labels later without a migration.
     */
    public function up()
    {
        if (Schema::hasTable('mobile_groups')) {
            return;
        }

        Schema::create('mobile_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_groups');
    }
}
