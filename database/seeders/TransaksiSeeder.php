<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('produk')->count() === 0) {
            $this->command->warn('Tabel produk kosong — jalankan seeder produk dulu sebelum TransaksiSeeder.');
            return;
        }

        $pelangganId = DB::table('pelanggan')->value('id');
        if (! $pelangganId) {
            $this->command->warn('Tabel pelanggan kosong — TransaksiSeeder butuh minimal 1 pelanggan.');
            return;
        }

        // id produk mengikuti urutan seeder produk: 1 Engine Mobil, 2 Engine Motor, 3 Carbon,
        // 4 Dish Soap, 5 Toilet, 6 Lantai, 7 Kaca, 8 HD Degreaser, 9 Chemical Floor
        // Pola belanja yang REALISTIS (bukan kombinasi acak) supaya Apriori punya pola untuk ditemukan:
        $polaBasket = [
            [1, 4, 5], [4, 5], [4, 5, 6], [1, 3], [4, 5, 7], [2, 1], [4, 5], [8, 9], [4, 6], [1, 3, 8],
            [1, 4, 5], [4, 5], [1, 3], [4, 5, 6], [2, 1], [4, 5], [1, 3], [4, 6], [4, 5, 7], [8, 9],
            [1, 4, 5], [4, 5], [4, 5, 6], [1, 3], [2, 1], [4, 5], [1, 3, 8], [4, 6], [4, 5], [1, 4, 5],
            [4, 5], [1, 3], [4, 5, 6], [4, 5], [2, 1], [1, 3], [4, 5, 7], [4, 6], [1, 4, 5], [4, 5],
            [8, 9], [1, 3], [4, 5], [4, 5, 6], [1, 4, 5], [4, 6], [2, 1], [4, 5], [1, 3, 8], [4, 5],
            [4, 5, 6], [1, 3], [4, 5], [1, 4, 5], [4, 6], [4, 5, 7], [1, 3], [4, 5], [2, 1], [4, 5, 6],
        ];

        $produkList = DB::table('produk')->get()->keyBy('id');
        $statuses = ['selesai', 'selesai', 'selesai', 'dikirim', 'diproses'];
        $metodes = ['va_bca', 'va_mandiri', 'qris', 'qris', 'manual'];

        foreach ($polaBasket as $i => $itemIds) {
            $no = $i + 1;
            $tanggal = now()->subDays(60 - $i);
            $subtotal = 0;
            $items = [];
            foreach ($itemIds as $pid) {
                $p = $produkList[$pid] ?? null;
                if (! $p) continue;
                $qty = rand(1, 2);
                $harga = (float) $p->harga;
                $subtotal += $harga * $qty;
                $items[] = ['produk_id' => $pid, 'nama_produk' => $p->nama_produk, 'harga_satuan' => $harga, 'qty' => $qty, 'subtotal' => $harga * $qty];
            }
            if (empty($items)) continue;

            $ongkir = 15000;
            $total = $subtotal + $ongkir;
            $status = $statuses[$i % count($statuses)];

            $transaksiId = DB::table('transaksi')->insertGetId([
                'no_order' => 'ORDER-SEED-' . str_pad($no, 4, '0', STR_PAD_LEFT),
                'pelanggan_id' => $pelangganId,
                'nama_penerima' => 'Pelanggan Simulasi ' . $no,
                'no_hp' => '0812' . str_pad((string) rand(1000000, 9999999), 8, '0', STR_PAD_LEFT),
                'alamat_kirim' => 'Alamat simulasi seeder no. ' . $no,
                'subtotal' => $subtotal, 'diskon_voucher' => 0, 'ongkir' => $ongkir, 'total' => $total,
                'status' => $status, 'created_at' => $tanggal, 'updated_at' => $tanggal,
            ]);

            foreach ($items as $it) {
                DB::table('detail_transaksi')->insert(array_merge($it, ['transaksi_id' => $transaksiId]));
            }

            DB::table('pembayaran')->insert([
                'transaksi_id' => $transaksiId, 'metode' => $metodes[$i % count($metodes)],
                'status' => $status === 'menunggu_pembayaran' ? 'menunggu' : 'diverifikasi',
                'created_at' => $tanggal,
            ]);

            if (in_array($status, ['diproses', 'dikemas', 'dikirim', 'selesai'])) {
                foreach ($items as $it) {
                    DB::table('produk')->where('id', $it['produk_id'])->decrement('stok', $it['qty']);
                    DB::table('produk')->where('id', $it['produk_id'])->increment('terjual', $it['qty']);
                }
            }
        }

        $this->command->info(count($polaBasket) . ' transaksi simulasi berhasil dibuat untuk keperluan analisis Apriori.');
    }
}
