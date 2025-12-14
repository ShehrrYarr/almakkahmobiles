<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\vendor;
use App\Services\GreenApi;

class SendReceivableVendorsWhatsApp extends Command
{
    protected $signature = 'whatsapp:receivables';
    protected $description = 'Send WhatsApp message to vendors who owe money (Debit > Credit)';

    public function handle(GreenApi $greenApi)
    {
        // Get vendors with positive balance (Debit - Credit > 0)
        $owing = DB::table('accounts')
            ->select(
                'vendor_id',
                DB::raw('SUM(Debit) AS total_debit'),
                DB::raw('SUM(Credit) AS total_credit'),
                DB::raw('(SUM(Debit) - SUM(Credit)) AS amount_owed')
            )
            ->whereNotNull('vendor_id')
            ->groupBy('vendor_id')
            ->havingRaw('(SUM(Debit) - SUM(Credit)) > 0')
            ->get();

        if ($owing->isEmpty()) {
            $this->info("No receivable vendors found.");
            return 0;
        }

        // Load vendor details
        $vendors = vendor::whereIn('id', $owing->pluck('vendor_id'))->get()->keyBy('id');

        $sent = 0;

        foreach ($owing as $row) {
            $v = $vendors->get($row->vendor_id);
            if (!$v) continue;

            // Convert mobile_no to chatId
            $mobile = preg_replace('/\D+/', '', $v->mobile_no ?? '');
            if (!$mobile) {
                $this->warn("Skip vendor {$v->id} (no mobile_no)");
                continue;
            }

            // If number starts with 0, convert to 92xxxxxxxxxx
            if (str_starts_with($mobile, '0')) {
                $mobile = '92' . substr($mobile, 1);
            }

            $chatId = $mobile . '@c.us';

            $amount = (int)$row->amount_owed;

            $message =
                "Assalam-o-Alaikum {$v->name},\n" .
                "Ap kay Total  Rs {$amount} Bakaya hen. \n" .
                "Baraye Meharbani Paisy Jamma karwain. Shukria.";

            try {
                $greenApi->sendText($chatId, $message);
                $sent++;
                $this->info("Sent to {$v->name} ({$chatId}) amount={$amount}");
            } catch (\Throwable $e) {
                $this->error("Failed vendor {$v->id}: " . $e->getMessage());
            }
        }

        $this->info("Done. Total sent: {$sent}");
        return 0;
    }
}
