<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    public $timestamps = false;
    protected $fillable = [
        'transaksi_id', 'metode', 'status', 'bukti_transfer', 'no_va',
        'midtrans_order_id', 'midtrans_transaction_id', 'verified_by', 'verified_at', 'created_at',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}
