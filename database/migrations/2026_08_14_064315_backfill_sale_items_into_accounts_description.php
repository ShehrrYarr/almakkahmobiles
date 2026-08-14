<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillSaleItemsIntoAccountsDescription extends Migration
{
    /**
     * Rebuild vendor-ledger descriptions for existing "Sale Invoice #N" entries
     * to include the sold items (name x qty @ price), and backfill the sale_id
     * column (never actually populated despite existing) by parsing the invoice
     * number out of the description.
     *
     * @return void
     */
    public function up()
    {
        DB::table('accounts')
            ->where('description', 'like', 'Sale Invoice #%')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    if (!preg_match('/Sale Invoice #(\d+)/', $row->description, $m)) {
                        continue;
                    }
                    $saleId = (int) $m[1];

                    $sale = DB::table('sales')->where('id', $saleId)->first();
                    if (!$sale) {
                        continue;
                    }

                    $items = DB::table('sale_items')
                        ->leftJoin('accessories', 'accessories.id', '=', 'sale_items.accessory_id')
                        ->where('sale_items.sale_id', $saleId)
                        ->select('sale_items.quantity', 'sale_items.price_per_unit', 'accessories.name')
                        ->get();

                    if ($items->isEmpty()) {
                        continue;
                    }

                    $summaries = $items->map(function ($item) {
                        $name = $item->name ?: 'Item';
                        return "{$name} x{$item->quantity} @ Rs." . number_format((float) $item->price_per_unit, 0);
                    })->implode(', ');

                    DB::table('accounts')->where('id', $row->id)->update([
                        'sale_id'     => $saleId,
                        'description' => "Sale Invoice #{$saleId} — {$summaries}",
                    ]);
                }
            });
    }

    /**
     * No sensible rollback for a data backfill — original descriptions aren't retained.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
