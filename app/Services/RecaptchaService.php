<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    // Daftar gratis di https://www.google.com/recaptcha/admin — pilih reCAPTCHA v2 "I'm not a robot".
    public static function verify(?string $token): bool
    {
        $secret = config('services.recaptcha.secret');

        if (empty($secret)) {
            return true; // Secret belum diisi -> captcha dilewati (mode development).
        }
        if (empty($token)) {
            return false;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
        ]);

        return (bool) ($response->json('success') ?? false);
    }
}
