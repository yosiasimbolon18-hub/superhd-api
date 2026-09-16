<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = ['role_id', 'nama', 'username', 'email', 'password', 'is_active'];
    protected $hidden = ['password'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
