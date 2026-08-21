<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RestructureMobileUnitsTable extends Migration
{
    /**
     * Drops the catalog/vendor relationship (mobile_id, mobile_vendor_id)
     * and the vendor-batch payment tracking (purchase_batch) in favor of:
     * shop scoping, a plain free-text mobile name typed per purchase, a
     * second optional IMEI, a has_box flag, and the seller's own details
     * captured directly on the unit (no reusable "vendor" record).
     */
    public function up()
    {
        if (!Schema::hasTable('mobile_units') || !Schema::hasColumn('mobile_units', 'mobile_id')) {
            return;
        }

        Schema::table('mobile_units', function (Blueprint $table) {
            $table->dropForeign(['mobile_id']);
            $table->dropForeign(['mobile_vendor_id']);
            if (Schema::hasColumn('mobile_units', 'purchase_batch')) {
                $table->dropColumn('purchase_batch');
            }
            $table->dropColumn(['mobile_id', 'mobile_vendor_id']);
        });

        Schema::table('mobile_units', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_id')->nullable()->after('id');
            $table->string('name')->after('shop_id');
            $table->string('imei2')->nullable()->unique()->after('imei');
            $table->boolean('has_box')->default(false)->after('battery_cycle');

            $table->string('seller_name')->nullable()->after('description');
            $table->string('seller_cnic')->nullable()->after('seller_name');
            $table->string('seller_phone')->nullable()->after('seller_cnic');
            $table->string('seller_address')->nullable()->after('seller_phone');
            $table->text('seller_description')->nullable()->after('seller_address');

            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
        });

        // PTA status no longer has a "JV" option.
        DB::statement("ALTER TABLE mobile_units MODIFY pta_status ENUM('PTA', 'Non PTA') NOT NULL");
    }

    public function down()
    {
        // Forward-only — the old vendor/catalog-linked structure is retired.
    }
}
