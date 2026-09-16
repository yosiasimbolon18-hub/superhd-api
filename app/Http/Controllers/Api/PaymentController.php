<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function charge(Request $request, $transaksiId)
    {
        $transaksi = Transaksi::with('detail')->findOrFail($transaksiId);
        $params = [
            'transaction_details' => ['order_id' => $transaksi->no_order, 'gross_amount' => (int) $transaksi->total],
            'customer_details' => ['first_name' => $transaksi->nama_penerima, 'phone' => $transaksi->no_hp, 'email' => $transaksi->email],
            'item_details' => $transaksi->detail->map(fn ($d) => [
                'id' => $d->produk_id, 'price' => (int) $d->harga_satuan, 'quantity' => $d->qty, 'name' => $d->nama_produk,
            ])->toArray(),
        ];
        $snapToken = Snap::getSnapToken($params);

        Pembayaran::updateOrCreate(
            ['transaksi_id' => $transaksi->id],
            ['metode' => 'manual', 'status' => 'menunggu', 'midtrans_order_id' => $transaksi->no_order, 'created_at' => now()]
        );

        return response()->json(['snap_token' => $snapToken, 'client_key' => config('services.midtrans.client_key')]);
    }

    public function notification(Request $request)
    {
        $notif = new Notification();
        $transaksi = Transaksi::where('no_order', $notif->order_id)->firstOrFail();
        $pembayaran = Pembayaran::where('transaksi_id', $transaksi->id)->firstOrFail();

        $status = match ($notif->transaction_status) {
            'capture', 'settlement' => 'dibayar',
            'pending' => 'menunggu',
            'deny', 'cancel', 'expire' => 'ditolak',
            default => 'menunggu',
        };
        $pembayaran->update(['status' => $status, 'midtrans_transaction_id' => $notif->transaction_id]);

        if ($status === 'dibayar') {
            $this->kurangiStokJikaBelum($transaksi);
            WhatsAppService::send($transaksi->no_hp, "Halo {$transaksi->nama_penerima}, pembayaran pesanan {$transaksi->no_order} berhasil diterima via Midtrans. Pesanan segera diproses. - PT Super HD Kliner");
        } elseif ($status === 'ditolak') {
            WhatsAppService::send($transaksi->no_hp, "Halo {$transaksi->nama_penerima}, pembayaran pesanan {$transaksi->no_order} tidak berhasil/dibatalkan. - PT Super HD Kliner");
        }

        return response()->json(['message' => 'Notifikasi diproses']);
    }

    // Dipakai bersama oleh notification() (Midtrans) & PesananController::verifikasiPembayaran() (manual/COD),
    // dijaga IDEMPOTEN via status transaksi supaya stok TIDAK dobel-kurang kalau dipanggil >1 kali.
    public static function kurangiStokJikaBelum(Transaksi $transaksi): void
    {
        if ($transaksi->status !== 'menunggu_pembayaran') {
            return; // sudah pernah diproses sebelumnya, jangan kurangi stok lagi
        }
        DB::transaction(function () use ($transaksi) {
            foreach ($transaksi->detail as $item) {
                Produk::where('id', $item->produk_id)->decrement('stok', $item->qty);
                Produk::where('id', $item->produk_id)->increment('terjual', $item->qty);
            }
            $transaksi->update(['status' => 'diproses']);
        });
    }
}
