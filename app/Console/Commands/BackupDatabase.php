<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Backup database MySQL ke file .sql di storage/app/backups';

    public function handle()
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $filename = 'backup_' . now()->format('Y-m-d_His') . '.sql';
        $path = storage_path('app/backups/' . $filename);

        if (! is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        // Windows (Laragon): mysqldump biasanya ada di PATH setelah instalasi. Kalau belum kebaca,
        // ganti 'mysqldump' di bawah dengan path lengkap, mis. C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe
        $passwordFlag = $password ? "-p{$password}" : '';
        $command = "mysqldump -h {$host} -u {$username} {$passwordFlag} {$database} > \"{$path}\"";

        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            $this->info("Backup berhasil: storage/app/backups/{$filename}");
        } else {
            $this->error('Backup gagal. Pastikan mysqldump ada di PATH sistem.');
        }
    }
}
