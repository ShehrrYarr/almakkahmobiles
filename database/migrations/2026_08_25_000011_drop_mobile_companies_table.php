<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropMobileCompaniesTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('mobile_companies');
    }

    public function down()
    {
        // Intentionally not recreated.
    }
}
