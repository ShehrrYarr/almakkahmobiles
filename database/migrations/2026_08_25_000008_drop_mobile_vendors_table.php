<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropMobileVendorsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('mobile_vendors');
    }

    public function down()
    {
        // Intentionally not recreated — vendor management for Mobile is retired.
    }
}
