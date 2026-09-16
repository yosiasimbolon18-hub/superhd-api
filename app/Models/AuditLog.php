<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_log';
    public $timestamps = false;
    protected $fillable = ['user_id', 'username', 'aksi', 'ip_address', 'created_at'];

    public static function catat(string $username, string $aksi, ?int $userId = null): void
    {
        static::create([
            'user_id' => $userId,
            'username' => $username,
            'aksi' => $aksi,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
