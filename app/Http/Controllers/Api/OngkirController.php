<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OngkirController extends Controller
{
    // Daftar gratis di https://rajaongkir.com (paket Starter cukup untuk JNE/POS/TIKI domestik).
    // POST /api/ongkir/cek  { destination_id, weight_gram, courier }
    public function cek(Request $request)
    {
        $apiKey = config('services.rajaongkir.key');

        if (empty($apiKey)) {
            // Fallback: ongkir flat kalau API key belum diisi, supaya checkout tetap bisa dites.
            return response()->json(['ongkir' => 15000, 'note' => 'RajaOngkir belum dikonfigurasi, pakai ongkir flat.']);
        }

        $request->validate([
            'destination_id' => 'required',
            'weight_gram' => 'required|integer|min:1',
            'courier' => 'required|string',
        ]);

        $response = Http::withHeaders(['key' => $apiKey])
            ->asForm()
            ->post('https://api.rajaongkir.com/starter/cost', [
                'origin' => config('services.rajaongkir.origin_id', '1'), // ID kota asal gudang, cek dokumentasi RajaOngkir
                'destination' => $request->destination_id,
                'weight' => $request->weight_gram,
                'courier' => $request->courier,
            ]);

        return response()->json($response->json());
    }

    public function kota()
    {
        $apiKey = config('services.rajaongkir.key');
        if (empty($apiKey)) {
            return response()->json(['message' => 'RajaOngkir belum dikonfigurasi'], 200);
        }
        $response = Http::withHeaders(['key' => $apiKey])->get('https://api.rajaongkir.com/starter/city');
        return response()->json($response->json());
    }
}
