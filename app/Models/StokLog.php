<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokLog extends Model
{
    protected $table = 'stok_log';
    public $timestamps = false;
    protected $fillable = ['produk_id', 'jenis', 'jumlah', 'keterangan', 'user_id', 'created_at'];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
