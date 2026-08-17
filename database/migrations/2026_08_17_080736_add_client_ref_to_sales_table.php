<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientRefToSalesTable extends Migration
{
    /**
     * Client-generated idempotency key. The POS sends the same key on every
     * retry of a given checkout attempt (initial request, offline queue,
     * and every later sync retry all reuse it), so a lost response followed
     * by a retry can be recognized as "already created" instead of creating
     * a duplicate sale.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('sales', 'client_ref')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->string('client_ref', 64)->nullable()->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['client_ref']);
            $table->dropColumn('client_ref');
        });
    }
}
