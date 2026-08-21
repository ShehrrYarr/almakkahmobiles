<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureMobileTablesUseInnodb extends Migration
{
    /**
     * This server's MySQL default_storage_engine is MyISAM, so every
     * earlier `Schema::create()` call for the Mobile tables (and this
     * migration's own shops table) silently landed on MyISAM instead of
     * InnoDB — MyISAM accepts FOREIGN KEY syntax without error but never
     * actually enforces it, then a *real* InnoDB table trying to reference
     * one of these (e.g. users.shop_id -> shops.id) fails outright since
     * InnoDB refuses to link to a non-InnoDB table. Converting these to
     * InnoDB first (matching `users`, which already is InnoDB) makes every
     * foreign key added by the migrations after this one actually work.
     */
    public function up()
    {
        foreach ([
            'shops', 'mobile_units', 'mobile_sales', 'mobile_held_orders',
            'mobile_banks', 'mobile_images', 'mobile_sale_items',
            'mobile_sale_payments', 'mobile_sale_returns', 'mobile_sale_return_items',
        ] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $engine = DB::selectOne('SHOW TABLE STATUS WHERE Name = ?', [$table])->Engine ?? null;
            if ($engine !== 'InnoDB') {
                DB::statement("ALTER TABLE `{$table}` ENGINE=InnoDB");
            }
        }
    }

    public function down()
    {
        // Not reversible — converting back to MyISAM would drop referential integrity.
    }
}
