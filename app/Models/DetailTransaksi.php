<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';
    public $timestamps = false;
    protected $fillable = ['transaksi_id', 'produk_id', 'nama_produk', 'harga_satuan', 'qty', 'subtotal'];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
