<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GreenApi
{
    private function endpoint(string $method): string
    {
        $base = rtrim(config('services.greenapi.url'), '/');
        $id   = config('services.greenapi.instance_id');
        $tok  = config('services.greenapi.token');

        return "{$base}/waInstance{$id}/{$method}/{$tok}";
    }

    public function sendText(string $chatId, string $message): array
    {
        $url = $this->endpoint('sendMessage');

        $resp = Http::timeout(20)->asJson()->post($url, [
            'chatId'  => $chatId,
            'message' => $message,
        ]);

        if (!$resp->successful()) {
            throw new \RuntimeException("GreenAPI {$resp->status()}: " . $resp->body());
        }

        return $resp->json();
    }
}
