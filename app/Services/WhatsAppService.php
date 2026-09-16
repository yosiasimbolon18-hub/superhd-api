<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    // Integrasi via Fonnte (fonnte.com) — daftar gratis, scan QR sekali buat hubungkan nomor WA.
    public static function send(string $phone, string $message): bool
    {
        $token = config('services.fonnte.token');

        if (empty($token)) {
            Log::info("[WhatsApp SIMULASI] Ke {$phone}: {$message}");
            return false; // Token belum diisi -> hanya dicatat ke log, tidak benar-benar terkirim.
        }

        $phone = preg_replace('/^0/', '62', preg_replace('/\D/', '', $phone));

        $response = Http::withHeaders(['Authorization' => $token])
            ->asForm()
            ->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => $message,
            ]);

        return $response->successful();
    }
}
