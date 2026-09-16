<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DistributorApprovedMail;
use App\Models\AuditLog;
use App\Models\Distributor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class DistributorController extends Controller
{
    public function index()
    {
        return response()->json(Distributor::orderByDesc('created_at')->get());
    }

    public function approve(Request $request, $id)
    {
        $distributor = Distributor::findOrFail($id);

        $username = 'dist' . $distributor->id;
        $tempPassword = 'dist' . Str::random(6);

        $distributor->update([
            'status' => 'approved',
            'username' => $username,
            'password' => Hash::make($tempPassword),
            'approved_by' => $request->user()->id,
        ]);

        AuditLog::catat($request->user()->username, "Menyetujui distributor \"{$distributor->nama_perusahaan}\" (akun: {$username})", $request->user()->id);

        $emailTerkirim = $this->kirimEmailKredensial($distributor, $tempPassword);

        return response()->json([
            'distributor' => $distributor,
            'username' => $username,
            'temp_password' => $tempPassword,
            'email_terkirim' => $emailTerkirim,
        ]);
    }

    public function reject(Request $request, $id)
    {
        $distributor = Distributor::findOrFail($id);
        $distributor->update(['status' => 'rejected']);
        AuditLog::catat($request->user()->username, "Menolak distributor \"{$distributor->nama_perusahaan}\"", $request->user()->id);
        return response()->json($distributor);
    }

    // Buat distributor lama yang belum punya email tercatat, atau mau kirim ulang kredensial.
    public function kirimUlangKredensial(Request $request, $id)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $distributor = Distributor::findOrFail($id);
        if ($distributor->status !== 'approved') {
            return response()->json(['message' => 'Distributor ini belum disetujui, tidak ada akun untuk dikirim.'], 422);
        }

        $distributor->update(['email' => $data['email']]);

        $tempPassword = 'dist' . Str::random(6);
        $distributor->update(['password' => Hash::make($tempPassword)]);

        AuditLog::catat($request->user()->username, "Mengirim ulang kredensial ke distributor \"{$distributor->nama_perusahaan}\"", $request->user()->id);

        $emailTerkirim = $this->kirimEmailKredensial($distributor, $tempPassword);

        return response()->json([
            'message' => $emailTerkirim
                ? 'Password baru sudah dikirim ke ' . $data['email']
                : 'Password berhasil di-reset tapi gagal kirim email — cek konfigurasi MAIL_* di .env',
            'email_terkirim' => $emailTerkirim,
        ]);
    }

    private function kirimEmailKredensial(Distributor $distributor, string $tempPassword): bool
    {
        if (empty($distributor->email)) {
            return false;
        }
        try {
            Mail::to($distributor->email)->send(new DistributorApprovedMail(
                $distributor->nama_perusahaan,
                $distributor->nama_pic,
                $distributor->username,
                $tempPassword,
            ));
            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal kirim email distributor: ' . $e->getMessage());
            return false;
        }
    }
}
