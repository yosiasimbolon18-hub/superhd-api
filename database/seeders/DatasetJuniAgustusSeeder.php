<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder dataset PENJUALAN dari 1 Juni 2026 s/d 31 Agustus 2026,
 * pakai nama-nama produk asli (sesuai foto yang sudah disiapkan).
 *
 * PENTING: seeder ini MENGHAPUS semua data transaksi & detail transaksi
 * LAMA dulu sebelum bikin yang baru (supaya datanya bersih, gak dobel
 * sama dataset Agustus 1-3 yang kemarin). Data produk TIDAK dihapus,
 * cuma ditambah kalau namanya belum ada.
 *
 * Cara jalanin (dari folder backend):
 *   php artisan db:seed --class=DatasetJuniAgustusSeeder
 */
class DatasetJuniAgustusSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- 0. Bersihin transaksi lama ----------
        DB::table('detail_transaksi')->delete();
        DB::table('transaksi')->delete();

        // ---------- 1. Kategori ----------
        $catAuto = Kategori::firstOrCreate(['nama_kategori' => 'Automotive Cleaner']);
        $catHousehold = Kategori::firstOrCreate(['nama_kategori' => 'Household Cleaner']);

        // ---------- 2. Produk (nama sesuai foto yang sudah disiapkan) ----------
        $produkList = [
            ['sku' => 'SHU-MTR-001', 'nama_produk' => 'Pembersih Kerak Mobil & Motor 250ML', 'kategori_id' => $catAuto->id, 'harga' => 18000, 'harga_distributor' => 15000],
            ['sku' => 'SHU-MTR-002', 'nama_produk' => 'Pembersih Kerak Mobil & Motor 5L', 'kategori_id' => $catAuto->id, 'harga' => 95000, 'harga_distributor' => 80000],
            ['sku' => 'SHU-MTR-003', 'nama_produk' => 'Shampo Mobil & Motor 5L', 'kategori_id' => $catAuto->id, 'harga' => 85000, 'harga_distributor' => 72000],
            ['sku' => 'SHU-TLT-001', 'nama_produk' => 'Pembersih Toilet 5L', 'kategori_id' => $catHousehold->id, 'harga' => 55000, 'harga_distributor' => 46000],
            ['sku' => 'SHU-KCA-001', 'nama_produk' => 'Pembersih Kaca 5L', 'kategori_id' => $catHousehold->id, 'harga' => 60000, 'harga_distributor' => 50000],
            ['sku' => 'SHU-SUN-005', 'nama_produk' => 'Sunlight 5L', 'kategori_id' => $catHousehold->id, 'harga' => 70000, 'harga_distributor' => 58000],
            ['sku' => 'SHU-SUN-001', 'nama_produk' => 'Sunlight 1L', 'kategori_id' => $catHousehold->id, 'harga' => 18000, 'harga_distributor' => 15000],
            ['sku' => 'SHU-CTG-001', 'nama_produk' => 'Sabun Cuci Tangan 500ML', 'kategori_id' => $catHousehold->id, 'harga' => 15000, 'harga_distributor' => 12500],
            ['sku' => 'SHU-CTG-005', 'nama_produk' => 'Cuci Tangan 5L', 'kategori_id' => $catHousehold->id, 'harga' => 65000, 'harga_distributor' => 54000],
            ['sku' => 'SHU-PWR-250', 'nama_produk' => 'Pewangi Ruangan 250ML', 'kategori_id' => $catHousehold->id, 'harga' => 22000, 'harga_distributor' => 18000],
            ['sku' => 'SHU-PWR-100', 'nama_produk' => 'Pewangi Ruangan 100ML', 'kategori_id' => $catHousehold->id, 'harga' => 12000, 'harga_distributor' => 10000],
            ['sku' => 'SHU-SPN-001', 'nama_produk' => 'Spons', 'kategori_id' => $catHousehold->id, 'harga' => 5000, 'harga_distributor' => 3000],
        ];

        $produkIds = [];
        foreach ($produkList as $p) {
            $produk = Produk::firstOrCreate(
                ['nama_produk' => $p['nama_produk']],
                [
                    'kategori_id' => $p['kategori_id'],
                    'sku' => $p['sku'],
                    'deskripsi' => $p['nama_produk'] . ' — produk pembersih Super HD Kliner.',
                    'harga' => $p['harga'],
                    'harga_distributor' => $p['harga_distributor'],
                    'min_order_dist' => 5,
                    'stok' => 1000,
                    'berat_kg' => 0.5,
                    'daya_angkat' => 80,
                    'is_active' => true,
                ]
            );
            $produkIds[$p['nama_produk']] = $produk->id;
        }

        // ---------- 3. Pola kombinasi produk (biar Apriori nemu pola jelas) ----------
        $pola = [
            // Perawatan mobil/motor sering dibeli bareng
            ['Pembersih Kerak Mobil & Motor 250ML', 'Shampo Mobil & Motor 5L'],
            ['Pembersih Kerak Mobil & Motor 5L', 'Shampo Mobil & Motor 5L'],
            ['Pembersih Kerak Mobil & Motor 250ML', 'Shampo Mobil & Motor 5L'],
            // Bersih-bersih rumah sering dibeli bareng
            ['Sunlight 1L', 'Spons'],
            ['Sunlight 5L', 'Spons'],
            ['Sunlight 1L', 'Sabun Cuci Tangan 500ML'],
            ['Pembersih Kaca 5L', 'Pewangi Ruangan 250ML'],
            ['Pembersih Toilet 5L', 'Pewangi Ruangan 100ML'],
            ['Cuci Tangan 5L', 'Sabun Cuci Tangan 500ML'],
            ['Pewangi Ruangan 250ML', 'Pewangi Ruangan 100ML'],
            // Beli satuan
            ['Sunlight 1L'],
            ['Spons'],
            ['Pembersih Toilet 5L'],
            ['Shampo Mobil & Motor 5L'],
            ['Sunlight 5L', 'Pembersih Kaca 5L'],
        ];

        $namaPenerima = [
            'Budi Santoso', 'Siti Aminah', 'Andi Wijaya', 'Rina Kartika',
            'Dedi Kurniawan', 'Maya Puspita', 'Agus Salim', 'Nurul Hidayah',
            'Rudi Hartono', 'Lestari Wulandari', 'Fajar Ramadhan', 'Indah Permata',
            'Hendra Gunawan', 'Yuni Astuti', 'Bambang Prasetyo',
        ];

        // ---------- 4. Generate transaksi harian dari 1 Juni s/d 31 Agustus 2026 ----------
        $mulai = strtotime('2026-06-01');
        $selesai = strtotime('2026-08-31');
        $counter = 1;
        $totalTransaksi = 0;

        for ($ts = $mulai; $ts <= $selesai; $ts += 86400) {
            $tanggal = date('Y-m-d', $ts);
            $jumlahOrderHariIni = rand(2, 5); // 2-5 transaksi per hari

            for ($n = 0; $n < $jumlahOrderHariIni; $n++) {
                $items = $pola[array_rand($pola)];
                $jam = sprintf('%02d:%02d:00', rand(8, 20), rand(0, 59));
                $waktu = $tanggal . ' ' . $jam;
                $penerima = $namaPenerima[array_rand($namaPenerima)];
                $noOrder = 'SHD-' . date('Ymd', $ts) . '-' . str_pad($counter, 5, '0', STR_PAD_LEFT);
                $counter++;

                $subtotal = 0;
                $detailRows = [];
                foreach ($items as $namaProduk) {
                    $p = collect($produkList)->firstWhere('nama_produk', $namaProduk);
                    $qty = rand(1, 3);
                    $lineTotal = $p['harga'] * $qty;
                    $subtotal += $lineTotal;
                    $detailRows[] = [
                        'produk_id' => $produkIds[$namaProduk],
                        'nama_produk' => $namaProduk,
                        'harga_satuan' => $p['harga'],
                        'qty' => $qty,
                        'subtotal' => $lineTotal,
                    ];
                }

                $ongkir = 15000;
                $total = $subtotal + $ongkir;

                $transaksiId = DB::table('transaksi')->insertGetId([
                    'no_order' => $noOrder,
                    'pelanggan_id' => null,
                    'distributor_id' => null,
                    'promo_id' => null,
                    'nama_penerima' => $penerima,
                    'no_hp' => '08' . rand(1111111111, 9999999999),
                    'email' => strtolower(str_replace(' ', '.', $penerima)) . '@example.com',
                    'alamat_kirim' => 'Jl. Contoh Dataset No. ' . rand(1, 99) . ', Jakarta',
                    'catatan' => null,
                    'subtotal' => $subtotal,
                    'diskon_voucher' => 0,
                    'ongkir' => $ongkir,
                    'total' => $total,
                    'status' => 'selesai',
                    'created_at' => $waktu,
                    'updated_at' => $waktu,
                ]);

                foreach ($detailRows as $d) {
                    $d['transaksi_id'] = $transaksiId;
                    DB::table('detail_transaksi')->insert($d);
                }
                $totalTransaksi++;
            }
        }

        $this->command->info("Dataset penjualan 1 Juni - 31 Agustus 2026 berhasil dibuat: {$totalTransaksi} transaksi.");
    }
}
