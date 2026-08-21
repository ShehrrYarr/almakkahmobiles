<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropMobileAccountsTable extends Migration
{
    /**
     * The mobile vendor ledger is being removed entirely — the client
     * doesn't want vendor management or a vendor ledger for the Mobile
     * section. Confirmed no real purchases/sales exist yet on production,
     * so this is safe to drop outright rather than migrate.
     */
    public function up()
    {
        Schema::dropIfExists('mobile_accounts');
    }

    public function down()
    {
        // Intentionally not recreated — this table is permanently retired.
    }
}
