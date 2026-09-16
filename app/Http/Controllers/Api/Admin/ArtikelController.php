<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\AuditLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    public function index()
    {
        return response()->json(Artikel::orderByDesc('created_at')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:200',
            'ringkasan' => 'nullable|string|max:255',
            'konten' => 'required|string',
        ]);

        $data['slug'] = Str::slug($data['judul']) . '-' . uniqid();
        $data['penulis_id'] = $request->user()->id;
        $data['is_published'] = true;

        $artikel = Artikel::create($data);
        AuditLog::catat($request->user()->username, "Mempublikasikan artikel \"{$artikel->judul}\"", $request->user()->id);

        return response()->json($artikel, 201);
    }

    public function destroy(Request $request, $id)
    {
        $artikel = Artikel::findOrFail($id);
        AuditLog::catat($request->user()->username, "Menghapus artikel \"{$artikel->judul}\"", $request->user()->id);
        $artikel->delete();
        return response()->json(['message' => 'Artikel dihapus']);
    }
}
