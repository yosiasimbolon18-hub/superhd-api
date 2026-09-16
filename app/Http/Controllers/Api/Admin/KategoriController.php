<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        return response()->json(Kategori::withCount('produk')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate(['nama_kategori' => 'required|string|max:100|unique:kategori,nama_kategori']);
        $kategori = Kategori::create($data);
        AuditLog::catat($request->user()->username, "Menambahkan kategori \"{$kategori->nama_kategori}\"", $request->user()->id);
        return response()->json($kategori, 201);
    }

    public function update(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);
        $data = $request->validate(['nama_kategori' => 'required|string|max:100|unique:kategori,nama_kategori,' . $id]);
        $kategori->update($data);
        AuditLog::catat($request->user()->username, "Mengubah kategori menjadi \"{$kategori->nama_kategori}\"", $request->user()->id);
        return response()->json($kategori);
    }

    public function destroy(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);
        if ($kategori->produk()->exists()) {
            return response()->json(['message' => 'Kategori masih dipakai produk, tidak bisa dihapus'], 422);
        }
        AuditLog::catat($request->user()->username, "Menghapus kategori \"{$kategori->nama_kategori}\"", $request->user()->id);
        $kategori->delete();
        return response()->json(['message' => 'Kategori dihapus']);
    }
}
