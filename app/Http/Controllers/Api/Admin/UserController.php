<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::with('role')->orderBy('nama')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = true;

        $user = User::create($data);
        AuditLog::catat($request->user()->username, "Membuat akun admin baru \"{$user->username}\"", $request->user()->id);

        return response()->json($user->load('role'), 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'nama' => 'sometimes|string|max:100',
            'role_id' => 'sometimes|exists:roles,id',
            'is_active' => 'sometimes|boolean',
        ]);
        $user->update($data);
        AuditLog::catat($request->user()->username, "Mengubah data admin \"{$user->username}\"", $request->user()->id);
        return response()->json($user->load('role'));
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $newPassword = 'admin' . rand(1000, 9999);
        $user->update(['password' => Hash::make($newPassword)]);
        AuditLog::catat($request->user()->username, "Reset password admin \"{$user->username}\"", $request->user()->id);
        // Password baru dikembalikan sekali di response ini supaya admin bisa sampaikan manual.
        return response()->json(['message' => 'Password direset', 'new_password' => $newPassword]);
    }

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Tidak bisa menghapus akun sendiri'], 422);
        }
        AuditLog::catat($request->user()->username, "Menghapus akun admin \"{$user->username}\"", $request->user()->id);
        $user->delete();
        return response()->json(['message' => 'Akun admin dihapus']);
    }
}
