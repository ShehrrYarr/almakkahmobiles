<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileAccountsTable extends Migration
{
    /**
     * Mirrors the accessory `accounts` ledger table exactly (same
     * Debit/Credit mechanics), scoped to mobile_vendors. Uses lowercase
     * debit/credit (the original accounts table's Debit/Credit casing was a
     * pre-existing inconsistency, not worth carrying into a new table).
     */
    public function up()
    {
        if (Schema::hasTable('mobile_accounts')) {
            return;
        }

        Schema::create('mobile_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mobile_vendor_id');
            $table->unsignedBigInteger('mobile_unit_id')->nullable();
            $table->unsignedBigInteger('mobile_sale_id')->nullable();
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->string('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('mobile_vendor_id')->references('id')->on('mobile_vendors')->onDelete('cascade');
            $table->foreign('mobile_unit_id')->references('id')->on('mobile_units')->onDelete('set null');
            $table->foreign('mobile_sale_id')->references('id')->on('mobile_sales')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_accounts');
    }
}
