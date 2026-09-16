<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $table = 'promo';
    public $timestamps = false;
    protected $fillable = ['kode_voucher', 'deskripsi', 'tipe', 'nilai', 'berlaku_dari', 'berlaku_sampai', 'is_active', 'created_at'];

    public function hitungDiskon(float $subtotal): float
    {
        return $this->tipe === 'persen' ? round($subtotal * $this->nilai / 100) : (float) $this->nilai;
    }
}
