<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'review';
    public $timestamps = false;
    protected $fillable = ['produk_id', 'pelanggan_id', 'rating', 'komentar', 'foto', 'created_at'];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }
}
