<?php
namespace App\Exports;

use App\Models\Transaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PenjualanExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Transaksi::with('detail')->orderByDesc('created_at')->get();
    }

    public function headings(): array
    {
        return ['No. Order', 'Tanggal', 'Nama Penerima', 'Total', 'Status Bayar', 'Status Pesanan', 'Jumlah Item'];
    }

    public function map($transaksi): array
    {
        return [
            $transaksi->no_order,
            $transaksi->created_at->format('d-m-Y'),
            $transaksi->nama_penerima,
            $transaksi->total,
            $transaksi->pembayaran->status ?? '-',
            $transaksi->status,
            $transaksi->detail->sum('qty'),
        ];
    }
}
