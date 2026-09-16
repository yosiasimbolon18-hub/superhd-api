<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $fillable = [
        'kategori_id', 'sku', 'nama_produk', 'deskripsi', 'cara_pakai', 'spesifikasi',
        'komposisi', 'foto', 'harga', 'harga_distributor', 'min_order_dist',
        'diskon_persen', 'stok', 'berat_kg', 'daya_angkat', 'terjual', 'is_active',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function stokLogs()
    {
        return $this->hasMany(StokLog::class);
    }

    // Harga efektif tergantung tipe pembeli (customer vs distributor)
    public function hargaUntuk(string $tipePembeli): float
    {
        return $tipePembeli === 'distributor' ? (float) $this->harga_distributor : (float) $this->harga;
    }
}
