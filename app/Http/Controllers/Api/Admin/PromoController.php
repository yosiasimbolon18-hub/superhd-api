<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        return response()->json(Promo::orderByDesc('created_at')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_voucher' => 'required|string|max:30|unique:promo,kode_voucher',
            'deskripsi' => 'nullable|string',
            'tipe' => 'required|in:persen,nominal',
            'nilai' => 'required|numeric|min:0',
            'berlaku_dari' => 'nullable|date',
            'berlaku_sampai' => 'nullable|date',
        ]);
        $data['created_at'] = now();
        $promo = Promo::create($data);
        AuditLog::catat($request->user()->username, "Menambahkan voucher \"{$promo->kode_voucher}\"", $request->user()->id);
        return response()->json($promo, 201);
    }

    public function toggle(Request $request, $id)
    {
        $promo = Promo::findOrFail($id);
        $promo->update(['is_active' => ! $promo->is_active]);
        AuditLog::catat($request->user()->username, ($promo->is_active ? 'Mengaktifkan' : 'Menonaktifkan') . " voucher \"{$promo->kode_voucher}\"", $request->user()->id);
        return response()->json($promo);
    }

    public function destroy(Request $request, $id)
    {
        $promo = Promo::findOrFail($id);
        AuditLog::catat($request->user()->username, "Menghapus voucher \"{$promo->kode_voucher}\"", $request->user()->id);
        $promo->delete();
        return response()->json(['message' => 'Promo dihapus']);
    }
}
