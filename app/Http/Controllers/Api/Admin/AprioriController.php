<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AturanAsosiasi;
use App\Models\DetailTransaksi;
use App\Models\ItemsetFrequent;
use App\Models\Produk;
use App\Services\AprioriService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AprioriController extends Controller
{
    // GET /api/admin/apriori/jalankan?min_support=&min_confidence=&max_itemset=
    // Menjalankan Apriori atas data TRANSAKSI RIIL, lalu MENYIMPAN hasilnya ke
    // itemset_frequent & aturan_asosiasi (bukan cuma dikembalikan sebagai JSON sekali pakai).
    public function jalankan(Request $request)
    {
        $minSupport = (float) $request->get('min_support', 0.1);
        $minConfidence = (float) $request->get('min_confidence', 0.5);
        $maxItemset = (int) $request->get('max_itemset', 3);

        $rows = DetailTransaksi::select('transaksi_id', 'produk_id')->get();
        $grouped = [];
        foreach ($rows as $r) $grouped[$r->transaksi_id][] = $r->produk_id;
        $transactions = array_values(array_map(fn ($t) => array_values(array_unique($t)), $grouped));

        if (count($transactions) < 3) {
            return response()->json([
                'message' => 'Data transaksi belum cukup untuk analisis Apriori (minimal beberapa transaksi dengan >1 produk). Jalankan seeder transaksi, atau gunakan /admin/apriori/simulasi untuk contoh perhitungan dengan data dummy.',
                'total_transaksi' => count($transactions),
                'rules' => [],
            ], 200);
        }

        $result = (new AprioriService($transactions, $minSupport, $minConfidence, $maxItemset))->run();

        // --- PERSIST: transaksi -> Apriori -> itemset_frequent -> aturan_asosiasi ---
        $runId = now()->format('YmdHis') . '-' . Str::random(6);
        DB::transaction(function () use ($result, $runId) {
            AturanAsosiasi::where('is_active', true)->update(['is_active' => false]); // nonaktifkan hasil run lama
            foreach ($result['frequent_itemsets'] as $fi) {
                ItemsetFrequent::create([
                    'run_id' => $runId, 'produk_ids' => $fi['items'], 'k' => $fi['k'],
                    'support' => $fi['support'], 'created_at' => now(),
                ]);
            }
            foreach ($result['rules'] as $r) {
                AturanAsosiasi::create([
                    'run_id' => $runId, 'antecedent' => $r['antecedent'], 'consequent' => $r['consequent'],
                    'support' => $r['support'], 'confidence' => $r['confidence'], 'lift' => $r['lift'],
                    'is_active' => true, 'created_at' => now(),
                ]);
            }
        });

        $result = $this->lampirkanNamaProduk($result);
        $result['run_id'] = $runId;
        $result['tersimpan'] = true;

        return response()->json($result);
    }

    // GET /api/admin/apriori/hasil — ambil hasil run TERAKHIR yang tersimpan (tanpa hitung ulang)
    // Endpoint ini juga yang dipakai RecommendationController, supaya admin & customer pakai SATU sumber parameter yang sama.
    public function hasil()
    {
        $rules = AturanAsosiasi::where('is_active', true)->orderByDesc('confidence')->get();
        $itemsets = ItemsetFrequent::whereIn('run_id', $rules->pluck('run_id')->unique())->orderBy('k')->get();

        return response()->json([
            'run_id' => $rules->first()->run_id ?? null,
            'jumlah_aturan' => $rules->count(),
            'rules' => $this->formatRules($rules),
            'itemsets' => $this->formatItemsets($itemsets),
        ]);
    }

    // GET /api/admin/apriori/simulasi — contoh perhitungan pakai data dummy (khusus dokumentasi Bab III, TIDAK disimpan ke DB)
    public function simulasi(Request $request)
    {
        $minSupport = (float) $request->get('min_support', 0.2);
        $minConfidence = (float) $request->get('min_confidence', 0.5);
        $maxItemset = (int) $request->get('max_itemset', 3);

        $dummyTransactions = [
            [1, 4, 5], [4, 5], [4, 5, 6], [1, 3], [4, 5, 7],
            [2, 1], [4, 5], [8, 9], [4, 6], [1, 3, 8],
        ];

        $result = (new AprioriService($dummyTransactions, $minSupport, $minConfidence, $maxItemset))->run();
        $result = $this->lampirkanNamaProduk($result);
        $result['catatan'] = 'Data simulasi/dummy — HANYA untuk dokumentasi contoh perhitungan algoritma (Bab III), TIDAK disimpan ke tabel itemset_frequent/aturan_asosiasi dan TIDAK dipakai untuk rekomendasi pelanggan.';

        return response()->json($result);
    }

    private function formatItemsets($itemsets)
    {
        $produkMap = Produk::pluck('nama_produk', 'id');
        return $itemsets->map(function ($it) use ($produkMap) {
            return [
                'items' => $it->produk_ids, 'k' => $it->k, 'support' => (float) $it->support,
                'nama_produk' => array_map(fn ($id) => $produkMap[$id] ?? "Produk #{$id}", $it->produk_ids),
            ];
        });
    }
    private function formatRules($rules)
    {
        $produkMap = Produk::pluck('nama_produk', 'id');
        return $rules->map(function ($r) use ($produkMap) {
            return [
                'antecedent' => $r->antecedent, 'consequent' => $r->consequent,
                'support' => (float) $r->support, 'confidence' => (float) $r->confidence, 'lift' => (float) $r->lift,
                'antecedent_nama' => array_map(fn ($id) => $produkMap[$id] ?? "Produk #{$id}", $r->antecedent),
                'consequent_nama' => array_map(fn ($id) => $produkMap[$id] ?? "Produk #{$id}", $r->consequent),
            ];
        });
    }
    private function lampirkanNamaProduk(array $result): array
    {
        $produkMap = Produk::pluck('nama_produk', 'id');
        $mapItems = fn ($ids) => array_map(fn ($id) => $produkMap[$id] ?? "Produk #{$id}", $ids);
        foreach ($result['iterations'] as &$iter) {
            foreach ($iter['candidates'] as &$c) $c['nama_produk'] = $mapItems($c['items']);
            foreach ($iter['frequent'] as &$f) $f['nama_produk'] = $mapItems($f['items']);
        }
        foreach ($result['frequent_itemsets'] as &$fi) $fi['nama_produk'] = $mapItems($fi['items']);
        foreach ($result['rules'] as &$r) {
            $r['antecedent_nama'] = $mapItems($r['antecedent']);
            $r['consequent_nama'] = $mapItems($r['consequent']);
        }
        return $result;
    }
}
