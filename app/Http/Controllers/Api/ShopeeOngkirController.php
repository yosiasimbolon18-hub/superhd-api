<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopeeOngkirController extends Controller
{
    // 1. Ambil daftar wilayah untuk dropdown pencarian
    public function getWilayah()
    {
        $wilayah = DB::table('wilayah_ongkir')->select('id', 'provinsi', 'kabupaten_kota', 'kecamatan', 'tarif_per_kg')->get();
        return response()->json(['data' => $wilayah]);
    }

    // 2. Hitung ongkir otomatis berdasarkan wilayah tujuan & total berat barang di keranjang
    public function hitungOngkir(Request $request)
    {
        $request->validate([
            'wilayah_id' => 'required|exists:wilayah_ongkir,id',
            'total_berat_gram' => 'required|numeric|min:1'
        ]);

        $wilayah = DB::table('wilayah_ongkir')->where('id', $request->wilayah_id)->first();

        // Konversi gram ke kg, bulatkan ke atas (Contoh: 1200 gram = 1.2 kg -> dibulatkan jadi 2 kg)
        // Atau pakai ceil() agar standar kurir (1.1 kg dihitung 2 kg)
        $beratKg = ceil($request->total_berat_gram / 1000);
        if ($beratKg < 1) {
            $beratKg = 1; // Minimal 1 kg
        }

        // Hitung total ongkir = tarif per kg * jumlah kg
        $totalOngkir = $wilayah->tarif_per_kg * $beratKg;

        return response()->json([
            'data' => [
                'tujuan' => "{$wilayah->kecamatan}, {$wilayah->kabupaten_kota}",
                'berat_kg' => $beratKg,
                'tarif_per_kg' => $wilayah->tarif_per_kg,
                'total_ongkir' => $totalOngkir
            ]
        ]);
    }
}
