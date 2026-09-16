<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Produk;
use App\Models\Promo;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,id',
            'items.*.qty' => 'required|integer|min:1',
            'nama_penerima' => 'required|string',
            'no_hp' => 'required|string',
            'alamat_kirim' => 'required|string',
        ]);

        $user = $request->user();
        $tipePembeli = $user instanceof \App\Models\Distributor ? 'distributor' : 'customer';

        return DB::transaction(function () use ($request, $user, $tipePembeli) {
            $subtotal = 0;
            $lines = [];

            foreach ($request->items as $item) {
                $produk = Produk::lockForUpdate()->findOrFail($item['produk_id']);
                if ($produk->stok < $item['qty']) {
                    abort(422, "Stok {$produk->nama_produk} tidak mencukupi");
                }
                $harga = $produk->hargaUntuk($tipePembeli);
                $lineSubtotal = $harga * $item['qty'];
                $subtotal += $lineSubtotal;
                $lines[] = ['produk' => $produk, 'qty' => $item['qty'], 'harga' => $harga, 'subtotal' => $lineSubtotal];
            }

            $diskon = 0; $promo = null;
            if ($request->filled('voucher_code')) {
                $promo = Promo::where('kode_voucher', $request->voucher_code)->where('is_active', true)->first();
                if ($promo) $diskon = $promo->hitungDiskon($subtotal);
            }

            $ongkir = 15000;
            $total = max(0, $subtotal - $diskon) + $ongkir;

            $transaksi = Transaksi::create([
                'no_order' => 'ORDER-' . now()->format('Ymd') . '-' . str_pad((string) (Transaksi::count() + 1), 4, '0', STR_PAD_LEFT),
                'pelanggan_id' => $tipePembeli === 'customer' ? $user->id : null,
                'distributor_id' => $tipePembeli === 'distributor' ? $user->id : null,
                'promo_id' => $promo?->id,
                'nama_penerima' => $request->nama_penerima, 'no_hp' => $request->no_hp, 'email' => $request->email,
                'alamat_kirim' => $request->alamat_kirim, 'catatan' => $request->catatan,
                'subtotal' => $subtotal, 'diskon_voucher' => $diskon, 'ongkir' => $ongkir, 'total' => $total,
                'status' => 'menunggu_pembayaran',
            ]);

            foreach ($lines as $line) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id, 'produk_id' => $line['produk']->id,
                    'nama_produk' => $line['produk']->nama_produk, 'harga_satuan' => $line['harga'],
                    'qty' => $line['qty'], 'subtotal' => $line['subtotal'],
                ]);
                // NOTE: stok TIDAK dikurangi di sini. Baru dikurangi sekali, saat pembayaran
                // benar-benar terkonfirmasi (lihat PaymentController::notification & confirmCod di bawah),
                // dijaga idempoten lewat status transaksi supaya tidak dobel-kurang.
            }

            return response()->json($transaksi->load('detail'), 201);
        });
    }

    // POST /api/checkout/{transaksiId}/cod — konfirmasi metode COD (Bayar di Tempat).
    // Sebelumnya frontend cuma menampilkan "sukses" tanpa memanggil backend sama sekali.
    public function confirmCod(Request $request, $transaksiId)
    {
        $transaksi = Transaksi::findOrFail($transaksiId);

        Pembayaran::updateOrCreate(
            ['transaksi_id' => $transaksi->id],
            ['metode' => 'cod', 'status' => 'menunggu', 'created_at' => now()]
        );
        // Status transaksi tetap 'menunggu_pembayaran' -> baru berubah & stok dikurangi saat
        // admin memverifikasi pembayaran COD di lapangan (PesananController::verifikasiPembayaran).

        return response()->json(['message' => 'Pesanan COD dikonfirmasi, menunggu pembayaran saat barang diterima.', 'transaksi' => $transaksi]);
    }

    public function myOrders(Request $request)
    {
        $user = $request->user();
        $column = $user instanceof \App\Models\Distributor ? 'distributor_id' : 'pelanggan_id';
        $orders = Transaksi::with(['detail', 'pembayaran', 'pengiriman'])
            ->where($column, $user->id)->orderByDesc('created_at')->get();
        return response()->json($orders);
    }
}
