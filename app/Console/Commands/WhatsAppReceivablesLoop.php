<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WhatsAppReceivablesLoop extends Command
{
    protected $signature = 'whatsapp:receivables-loop {--sleep=1}';
    protected $description = 'Continuously run receivables WhatsApp sender every N seconds (testing)';

    public function handle()
    {
        $sleep = (int) $this->option('sleep');
        if ($sleep < 1) $sleep = 1;

        $this->info("Starting loop. Running every {$sleep} second(s). Press CTRL+C to stop.");

        while (true) {
            // call your working command
            $this->call('whatsapp:receivables');

            sleep($sleep);
        }
    }
}
