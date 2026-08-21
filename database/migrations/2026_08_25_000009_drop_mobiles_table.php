<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropMobilesTable extends Migration
{
    /**
     * The reusable "Mobile catalog" (name/company/condition) is retired —
     * the mobile name is now just a free-text field entered per purchase,
     * stored directly on mobile_units (see restructure_mobile_units_table,
     * which must run before this one).
     */
    public function up()
    {
        Schema::dropIfExists('mobiles');
    }

    public function down()
    {
        // Intentionally not recreated.
    }
}
