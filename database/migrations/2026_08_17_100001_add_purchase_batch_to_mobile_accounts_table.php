<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('mobile_accounts', 'purchase_batch')) {
            return;
        }

        Schema::table('mobile_accounts', function (Blueprint $table) {
            $table->string('purchase_batch', 36)->nullable()->after('mobile_unit_id')->index();
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('mobile_accounts', 'purchase_batch')) {
            return;
        }

        Schema::table('mobile_accounts', function (Blueprint $table) {
            $table->dropColumn('purchase_batch');
        });
    }
};
