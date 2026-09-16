<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        if (! RecaptchaService::verify($request->recaptcha_token)) {
            return response()->json(['message' => 'Verifikasi captcha gagal, coba lagi.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:pelanggan,username',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email|unique:pelanggan,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $pelanggan = Pelanggan::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $pelanggan->createToken('pelanggan-token')->plainTextToken;

        return response()->json(['user' => $pelanggan, 'role' => 'customer', 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        if (! RecaptchaService::verify($request->recaptcha_token)) {
            return response()->json(['message' => 'Verifikasi captcha gagal, coba lagi.'], 422);
        }

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'role' => 'required|in:customer,distributor,admin',
        ]);

        $model = match ($request->role) {
            'customer' => \App\Models\Pelanggan::class,
            'distributor' => \App\Models\Distributor::class,
            'admin' => \App\Models\User::class,
        };

        $account = $model::where('username', $request->username)->first();

        if (! $account || ! Hash::check($request->password, $account->password)) {
            return response()->json(['message' => 'Username atau password salah'], 401);
        }

        if ($request->role === 'distributor' && $account->status !== 'approved') {
            return response()->json(['message' => 'Akun distributor belum disetujui admin'], 403);
        }

        if ($request->role === 'admin' && ! $account->is_active) {
            return response()->json(['message' => 'Akun admin ini dinonaktifkan'], 403);
        }

        $token = $account->createToken('auth-token')->plainTextToken;

        return response()->json(['user' => $account, 'role' => $request->role, 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Berhasil keluar']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
