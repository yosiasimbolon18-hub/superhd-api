<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    protected $table = 'pengiriman';
    public $timestamps = false;
    protected $fillable = ['transaksi_id', 'nomor_resi', 'ekspedisi', 'tanggal_kirim', 'status_pengiriman', 'input_by', 'created_at'];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}
