<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AturanAsosiasi;
use App\Models\Produk;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    // GET /api/produk/{id}/rekomendasi — "Sering dibeli bersama" di halaman detail produk.
    // TIDAK ADA LAGI fallback data dummy — kalau belum ada aturan asosiasi tersimpan, kembalikan array kosong
    // + pesan yang jelas, supaya jujur ke pengguna/penguji bahwa rekomendasi berasal murni dari data riil.
    public function forProduct(Request $request, $id)
    {
        $rules = AturanAsosiasi::where('is_active', true)->get();

        if ($rules->isEmpty()) {
            return response()->json(['data' => [], 'message' => 'Belum tersedia rekomendasi. Data transaksi belum mencukupi untuk analisis Apriori.']);
        }

        $recommended = [];
        foreach ($rules as $rule) {
            if (in_array((int) $id, $rule->antecedent)) {
                foreach ($rule->consequent as $pid) {
                    $recommended[$pid] = max($recommended[$pid] ?? 0, (float) $rule->confidence);
                }
            }
        }
        arsort($recommended);
        $produkIds = array_slice(array_keys($recommended), 0, 4);

        if (empty($produkIds)) {
            return response()->json(['data' => [], 'message' => 'Belum ada aturan asosiasi yang relevan untuk produk ini.']);
        }

        $produk = Produk::whereIn('id', $produkIds)->where('is_active', true)->get();
        $produk->transform(function ($p) {
            $p->foto_url = $p->foto ? asset('storage/' . $p->foto) : null;
            return $p;
        });

        return response()->json(['data' => $produk, 'message' => null]);
    }

    // POST /api/produk/rekomendasi-keranjang  { produk_ids: [1,4,5] }
    // "Cocok Ditambahkan Juga" di halaman Keranjang Belanja — agregasi rule dari SELURUH isi keranjang.
    public function forCart(Request $request)
    {
        $request->validate(['produk_ids' => 'required|array|min:1']);
        $cartIds = array_map('intval', $request->produk_ids);

        $rules = AturanAsosiasi::where('is_active', true)->get();
        if ($rules->isEmpty()) {
            return response()->json(['data' => [], 'message' => 'Belum tersedia rekomendasi. Data transaksi belum mencukupi.']);
        }

        $recommended = [];
        foreach ($rules as $rule) {
            // Rule dipakai kalau SELURUH antecedent-nya sudah ada di keranjang (subset)
            if (count(array_diff($rule->antecedent, $cartIds)) === 0) {
                foreach ($rule->consequent as $pid) {
                    if (in_array($pid, $cartIds)) continue; // sudah ada di keranjang, skip
                    $recommended[$pid] = max($recommended[$pid] ?? 0, (float) $rule->confidence);
                }
            }
        }
        arsort($recommended);
        $produkIds = array_slice(array_keys($recommended), 0, 4);

        $produk = Produk::whereIn('id', $produkIds)->where('is_active', true)->get();
        $produk->transform(function ($p) {
            $p->foto_url = $p->foto ? asset('storage/' . $p->foto) : null;
            return $p;
        });

        return response()->json(['data' => $produk, 'message' => $produk->isEmpty() ? 'Belum ada rekomendasi tambahan untuk kombinasi produk ini.' : null]);
    }
}
