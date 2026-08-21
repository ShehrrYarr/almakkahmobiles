<?php

namespace App\Http\Controllers;

use App\Models\MobileSale;
use App\Models\MobileSaleItem;
use App\Models\MobileSalePayment;
use App\Models\MobileSaleReturn;
use App\Models\MobileSaleReturnItem;
use Illuminate\Http\Request;

class MobileSaleReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List the sale's items for the return-selection UI. A mobile unit is
     * serialized (qty always 1), so returning is a binary pick-per-item —
     * unlike accessory returns there's no partial-quantity concept.
     */
    public function itemsForSale($saleId)
    {
        $sale = MobileSale::with('items.unit')->where('shop_id', session('current_shop_id'))->find($saleId);

        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        $items = $sale->items->map(function ($item) {
            return [
                'id'          => $item->id,
                'mobile_name' => $item->unit->name ?? '-',
                'imei'        => $item->unit->imei ?? '-',
                'price'       => $item->price,
                'already_returned' => MobileSaleReturnItem::where('mobile_sale_item_id', $item->id)->exists(),
            ];
        });

        return response()->json(['success' => true, 'items' => $items]);
    }

    public function processReturn(Request $request, MobileSale $sale)
    {
        if ($sale->shop_id !== session('current_shop_id')) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        $data = $request->validate([
            'item_ids'   => 'required|array|min:1',
            'item_ids.*' => 'integer',
            'reason'     => 'nullable|string|max:255',
        ]);

        \DB::beginTransaction();
        try {
            $saleReturn = MobileSaleReturn::create([
                'mobile_sale_id' => $sale->id,
                'user_id'        => auth()->id(),
                'reason'         => $data['reason'] ?? null,
            ]);

            $totalReturnValue = 0.0;

            foreach ($data['item_ids'] as $itemId) {
                $item = MobileSaleItem::with('unit')->where('mobile_sale_id', $sale->id)->find($itemId);
                if (!$item) continue;

                $alreadyReturned = MobileSaleReturnItem::where('mobile_sale_item_id', $item->id)->exists();
                if ($alreadyReturned) continue;

                MobileSaleReturnItem::create([
                    'mobile_sale_return_id' => $saleReturn->id,
                    'mobile_sale_item_id'   => $item->id,
                    'refund_amount'         => $item->price,
                ]);

                if ($item->unit) {
                    $item->unit->status = 'in_stock';
                    $item->unit->save();
                }

                $totalReturnValue += (float) $item->price;
            }

            if ($totalReturnValue <= 0) {
                \DB::rollBack();
                return response()->json(['success' => false, 'message' => 'No items selected for return.'], 422);
            }

            $sale->total_amount = max(0, round((float) $sale->total_amount - $totalReturnValue, 2));
            $sale->save();

            $paidSoFar = (float) $sale->payments()->sum('amount');
            $refundToCash = min($totalReturnValue, max(0, $paidSoFar));
            if ($refundToCash > 0) {
                MobileSalePayment::create([
                    'mobile_sale_id' => $sale->id,
                    'method'         => 'counter',
                    'mobile_bank_id' => null,
                    'amount'         => -round($refundToCash, 2),
                    'reference_no'   => 'RETURN-' . $saleReturn->id,
                    'processed_by'   => auth()->id(),
                    'paid_at'        => now(),
                ]);
            }

            $sale->pay_amount = max(0, (float) $sale->payments()->sum('amount'));
            $sale->save();

            \DB::commit();

            return response()->json(['success' => true, 'refund_amount' => round($totalReturnValue, 2)]);
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('Mobile Sales Return Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
