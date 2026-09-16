<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Pelanggan extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'pelanggan';
    protected $fillable = ['nama', 'username', 'email', 'password', 'no_hp', 'alamat'];
    protected $hidden = ['password'];

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }
}
