<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Distributor extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'distributor';
    protected $fillable = ['nama_perusahaan', 'nama_pic', 'no_hp', 'email', 'alamat', 'npwp', 'username', 'password', 'status', 'approved_by'];
    protected $hidden = ['password'];

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
