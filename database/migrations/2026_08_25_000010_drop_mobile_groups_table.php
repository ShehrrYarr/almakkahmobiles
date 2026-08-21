<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropMobileGroupsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('mobile_groups');
    }

    public function down()
    {
        // Intentionally not recreated.
    }
}
