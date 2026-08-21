<?php

namespace App\Http\Controllers;

use App\Models\Mobile;
use App\Models\MobileAccount;
use App\Models\MobileImage;
use App\Models\MobileUnit;
use App\Models\MobileVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobilePurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Bulk-purchase screen: pick a vendor, add one or more phones (each with
     * its own IMEI etc.), submit as one transaction. Mirrors the accessory
     * bulk-batch flow, adapted for serialized units (no quantity field —
     * every unit is entered individually since every unit has a unique IMEI).
     */
    public function create()
    {
        $vendors = MobileVendor::orderBy('name')->get();
        $mobiles = Mobile::with(['company', 'group'])->get();

        return view('mobile.purchase', compact('vendors', 'mobiles'));
    }

    public function report(Request $request)
    {
        $start    = $request->input('start_date');
        $end      = $request->input('end_date');
        $vendorId = $request->input('vendor_id');

        $units = MobileUnit::with(['mobile.company', 'mobile.group', 'vendor', 'images'])
            ->when($start && $end, fn ($q) => $q->whereBetween('purchase_date', [$start, $end]))
            ->when($vendorId, fn ($q) => $q->where('mobile_vendor_id', $vendorId))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get();

        // Payment status is recorded per purchase batch (one vendor "Purchase Mobiles" submission),
        // not per unit — a lump pay_amount is split across every IMEI in that submission.
        $batchIds = $units->pluck('purchase_batch')->filter()->unique()->values();
        $batchTotals = DB::table('mobile_accounts')
            ->whereIn('purchase_batch', $batchIds)
            ->selectRaw('purchase_batch, SUM(credit) as total_credit, SUM(debit) as total_paid')
            ->groupBy('purchase_batch')
            ->get()
            ->keyBy('purchase_batch');

        $totalPurchaseAmount = (float) $units->sum('purchase_price');
        $totalSellingAmount  = (float) $units->sum('selling_price');

        $vendors = MobileVendor::orderBy('name')->get();

        return view('mobile.purchaseReport', compact(
            'units', 'batchTotals', 'totalPurchaseAmount', 'totalSellingAmount', 'vendors', 'start', 'end', 'vendorId'
        ));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'vendor_id'                  => 'required|exists:mobile_vendors,id',
            'pay_amount'                 => 'nullable|numeric|min:0',
            'items'                      => 'required|array|min:1',
            'items.*.mobile_id'          => 'required|exists:mobiles,id',
            'items.*.imei'               => 'required|string|max:32|distinct|unique:mobile_units,imei',
            'items.*.storage'            => 'nullable|string|max:50',
            'items.*.pta_status'         => 'required|in:PTA,Non PTA,JV',
            'items.*.battery'            => 'nullable|string|max:50',
            'items.*.battery_cycle'      => 'nullable|integer|min:0',
            'items.*.purchase_price'     => 'required|numeric|min:0',
            'items.*.selling_price'      => 'required|numeric|min:0',
            'items.*.purchase_date'      => 'required|date',
            'items.*.description'        => 'nullable|string',
            'items.*.images.*'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $vendorId  = (int) $request->vendor_id;
        $userId    = auth()->id();
        $items     = $request->items;
        $payAmount = (float) ($request->pay_amount ?? 0);

        DB::beginTransaction();
        try {
            $totalCredit = 0;
            $imeis = [];
            $batchId = (string) Str::uuid();

            foreach ($items as $index => $row) {
                $unit = MobileUnit::create([
                    'mobile_id'       => (int) $row['mobile_id'],
                    'mobile_vendor_id'=> $vendorId,
                    'imei'            => $row['imei'],
                    'storage'         => $row['storage'] ?? null,
                    'pta_status'      => $row['pta_status'],
                    'battery'         => $row['battery'] ?? null,
                    'battery_cycle'   => $row['battery_cycle'] ?? null,
                    'purchase_price'  => (float) $row['purchase_price'],
                    'selling_price'   => (float) $row['selling_price'],
                    'purchase_date'   => $row['purchase_date'],
                    'purchase_batch'  => $batchId,
                    'description'     => $row['description'] ?? null,
                    'status'          => 'in_stock',
                    'user_id'         => $userId,
                ]);

                // Up to 5 images for this unit
                $images = $request->file("items.$index.images", []);
                if (is_array($images)) {
                    foreach (array_slice($images, 0, 5) as $img) {
                        if ($img && $img->isValid()) {
                            $path = $img->store('mobile_units', 'public');
                            MobileImage::create(['mobile_unit_id' => $unit->id, 'path' => $path]);
                        }
                    }
                }

                $lineTotal = (float) $row['purchase_price'];
                $totalCredit += $lineTotal;
                $imeis[] = $unit->imei;

                MobileAccount::create([
                    'mobile_vendor_id' => $vendorId,
                    'mobile_unit_id'   => $unit->id,
                    'purchase_batch'   => $batchId,
                    'credit'           => $lineTotal,
                    'debit'            => 0,
                    'description'      => "Mobile Purchase: IMEI {$unit->imei} ({$row['purchase_price']})",
                    'created_by'       => $userId,
                ]);
            }

            if ($payAmount > 0) {
                MobileAccount::create([
                    'mobile_vendor_id' => $vendorId,
                    'purchase_batch'   => $batchId,
                    'credit'           => 0,
                    'debit'            => $payAmount,
                    'description'      => 'Payment for IMEIs: ' . implode(', ', $imeis),
                    'created_by'       => $userId,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'ok',
                'message' => 'Mobiles purchased successfully',
                'totals'  => ['credit' => $totalCredit, 'debit' => $payAmount],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
