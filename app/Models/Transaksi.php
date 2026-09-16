<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $fillable = [
        'no_order', 'pelanggan_id', 'distributor_id', 'promo_id', 'nama_penerima',
        'no_hp', 'email', 'alamat_kirim', 'catatan', 'subtotal', 'diskon_voucher',
        'ongkir', 'total', 'status',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }

    public function pengiriman()
    {
        return $this->hasOne(Pengiriman::class);
    }
}
