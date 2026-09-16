<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use App\Models\Transaksi;
use App\Http\Controllers\Api\PaymentController;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function index()
    {
        $pesanan = Transaksi::with(['detail', 'pembayaran', 'pengiriman', 'pelanggan', 'distributor'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($pesanan);
    }

    public function verifikasiPembayaran(Request $request, $id)
    {
        $transaksi = Transaksi::findOrFail($id);
        $pembayaran = Pembayaran::where('transaksi_id', $transaksi->id)->firstOrFail();

        $pembayaran->update([
            'status' => 'diverifikasi',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        // Dipakai bareng dengan webhook Midtrans (PaymentController::notification) — dijaga
        // IDEMPOTEN lewat status transaksi, jadi aman dipanggil dari dua jalur (VA/QRIS otomatis
        // via Midtrans, ATAU transfer manual/COD yang diverifikasi manual oleh admin di sini)
        // tanpa risiko stok kepotong dua kali untuk transaksi yang sama.
        PaymentController::kurangiStokJikaBelum($transaksi);

        AuditLog::catat($request->user()->username, "Verifikasi pembayaran pesanan #{$transaksi->no_order}", $request->user()->id);

        WhatsAppService::send(
            $transaksi->no_hp,
            "Halo {$transaksi->nama_penerima}, pembayaran untuk pesanan {$transaksi->no_order} sudah kami terima dan sedang diproses. Terima kasih! - PT Super HD Kliner"
        );

        return response()->json(['message' => 'Pembayaran diverifikasi, stok diperbarui (jika belum pernah dikurangi sebelumnya)']);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:menunggu_pembayaran,diproses,dikemas,dikirim,selesai']);

        $transaksi = Transaksi::findOrFail($id);
        $transaksi->update(['status' => $request->status]);

        AuditLog::catat($request->user()->username, "Update status pesanan #{$transaksi->no_order} menjadi {$request->status}", $request->user()->id);

        $statusLabel = [
            'menunggu_pembayaran' => 'menunggu pembayaran',
            'diproses' => 'sedang diproses',
            'dikemas' => 'sedang dikemas',
            'dikirim' => 'telah dikirim',
            'selesai' => 'selesai',
        ][$request->status] ?? $request->status;

        WhatsAppService::send(
            $transaksi->no_hp,
            "Halo {$transaksi->nama_penerima}, status pesanan {$transaksi->no_order} sekarang: {$statusLabel}. - PT Super HD Kliner"
        );

        return response()->json($transaksi);
    }

    public function inputResi(Request $request, $id)
    {
        $request->validate(['nomor_resi' => 'required|string', 'ekspedisi' => 'required|string']);

        $transaksi = Transaksi::findOrFail($id);

        $pengiriman = Pengiriman::updateOrCreate(
            ['transaksi_id' => $transaksi->id],
            [
                'nomor_resi' => $request->nomor_resi,
                'ekspedisi' => $request->ekspedisi,
                'tanggal_kirim' => now(),
                'status_pengiriman' => 'dikirim',
                'input_by' => $request->user()->id,
                'created_at' => now(),
            ]
        );

        $transaksi->update(['status' => 'dikemas']);

        AuditLog::catat($request->user()->username, "Input resi {$request->nomor_resi} untuk pesanan #{$transaksi->no_order}", $request->user()->id);

        WhatsAppService::send(
            $transaksi->no_hp,
            "Halo {$transaksi->nama_penerima}, pesanan {$transaksi->no_order} sudah dikirim via {$request->ekspedisi}. No. Resi: {$request->nomor_resi}. - PT Super HD Kliner"
        );

        return response()->json($pengiriman);
    }
}
