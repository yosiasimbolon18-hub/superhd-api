<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $table = 'wishlist';
    public $timestamps = false;
    protected $fillable = ['pelanggan_id', 'produk_id', 'created_at'];
}
