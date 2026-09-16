<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Exports\PenjualanExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function exportPdf()
    {
        $transaksi = Transaksi::with('detail')->orderByDesc('created_at')->get();
        $totalPendapatan = $transaksi->sum('total');

        $pdf = Pdf::loadView('laporan.penjualan', [
            'transaksi' => $transaksi,
            'total' => $totalPendapatan,
            'tanggal' => now()->format('d F Y'),
        ]);

        return $pdf->download('laporan-penjualan-' . now()->format('Ymd') . '.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new PenjualanExport, 'laporan-penjualan-' . now()->format('Ymd') . '.xlsx');
    }
}
