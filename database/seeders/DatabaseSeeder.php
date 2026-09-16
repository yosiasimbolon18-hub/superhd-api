<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['nama_role' => 'Super Admin', 'deskripsi' => 'Akses penuh ke seluruh modul sistem', 'created_at' => now(), 'updated_at' => now()],
            ['nama_role' => 'Admin', 'deskripsi' => 'Mengelola transaksi & pesanan', 'created_at' => now(), 'updated_at' => now()],
            ['nama_role' => 'Marketing', 'deskripsi' => 'Mengelola produk, promo, dan pelanggan', 'created_at' => now(), 'updated_at' => now()],
            ['nama_role' => 'Gudang', 'deskripsi' => 'Mengelola stok dan pengiriman', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('kategori')->insert([
            ['nama_kategori' => 'Automotive Cleaner', 'created_at' => now(), 'updated_at' => now()],
            ['nama_kategori' => 'Household Cleaner', 'created_at' => now(), 'updated_at' => now()],
            ['nama_kategori' => 'Industrial Cleaner', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('users')->insert([
            'role_id' => 1,
            'nama' => 'Admin Toko',
            'username' => 'admin',
            'email' => 'admin@superhdkliner.co.id',
            'password' => Hash::make('admin123'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('pelanggan')->insert([
            'nama' => 'Budi Santoso',
            'username' => 'pelanggan',
            'email' => 'budi@example.com',
            'password' => Hash::make('pelanggan123'),
            'no_hp' => '0812-1111-2222',
            'alamat' => 'Jl. Contoh No.123, Jakarta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // SISIPKAN SEEDER PRODUK ATAU DUMMY PRODUK DI SINI DULU:
        if (class_exists(ProdukSeeder::class)) {
            $this->call(ProdukSeeder::class);
        } else {
            // Jika ProdukSeeder tidak ada, kita buat 1 produk dummy otomatis agar TransaksiSeeder tidak error
            DB::table('produk')->insert([
                'kategori_id' => 1,
                'sku' => 'PRD-001',
                'nama_produk' => 'Super Cleaner',
                'harga' => 50000,
                'harga_distributor' => 40000,
                'stok' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->call(TransaksiSeeder::class);
    }
}