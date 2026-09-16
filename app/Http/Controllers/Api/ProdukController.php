<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::with('kategori')->where('is_active', true);

        if ($request->filled('kategori') && $request->kategori !== 'Semua') {
            $query->whereHas('kategori', fn ($q) => $q->where('nama_kategori', $request->kategori));
        }
        if ($request->filled('q')) {
            $query->where('nama_produk', 'like', '%' . $request->q . '%');
        }

        $query = match ($request->get('sort', 'terbaru')) {
            'terlaris' => $query->orderByDesc('terjual'),
            'harga-asc' => $query->orderBy('harga'),
            'harga-desc' => $query->orderByDesc('harga'),
            default => $query->orderByDesc('created_at'),
        };

        $result = $query->paginate(12);
        $result->getCollection()->transform(function ($p) {
            $p->foto_url = $p->foto ? asset('storage/' . $p->foto) : null;
            return $p;
        });

        return response()->json($result);
    }

    public function show($id)
    {
        $produk = Produk::with(['kategori', 'reviews.pelanggan'])->findOrFail($id);
        $produk->foto_url = $produk->foto ? asset('storage/' . $produk->foto) : null;
        return response()->json($produk);
    }

    public function store(Request $request)
    {
        // Debug sementara: abaikan validasi foto biar masuk database dulu
        $data = $request->except(['foto']);
        
        $data['kategori_id'] = $request->kategori_id;
        $data['sku'] = $request->sku;
        $data['nama_produk'] = $request->nama_produk;
        $data['harga'] = $request->harga;
        $data['harga_distributor'] = $request->harga_distributor;
        $data['stok'] = $request->stok;
        $data['deskripsi'] = $request->deskripsi ?? '';
        
        // Kalau ada file yang masuk lewat $_FILES mentah PHP
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['foto']['tmp_name'];
            $fileName = time() . '_' . $_FILES['foto']['name'];
            $destDir = public_path('storage/produk');
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }
            move_uploaded_file($tmpName, $destDir . '/' . $fileName);
            $data['foto'] = 'produk/' . $fileName;
        } else {
            $data['foto'] = null;
        }

        $produk = Produk::create($data);
        $produk->foto_url = $produk->foto ? asset('storage/' . $produk->foto) : null;

        if ($request->user()) {
            AuditLog::catat($request->user()->username, "Menambahkan produk baru \"{$produk->nama_produk}\" (SKU: {$produk->sku})", $request->user()->id);
        }

        return response()->json($produk, 201);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $data = $request->validate([
            'nama_produk' => 'sometimes|string|max:150',
            'kategori_id' => 'sometimes|exists:kategori,id',
            'deskripsi' => 'nullable|string',
            'cara_pakai' => 'nullable|string',
            'spesifikasi' => 'nullable|string',
            'komposisi' => 'nullable|string',
            'harga' => 'sometimes|numeric',
            'harga_distributor' => 'sometimes|numeric',
            'min_order_dist' => 'sometimes|integer|min:1',
            'diskon_persen' => 'sometimes|integer|min:0|max:100',
            'stok' => 'sometimes|integer|min:0',
            'berat_kg' => 'sometimes|numeric',
            'daya_angkat' => 'sometimes|integer|min:0|max:100',
            'is_active' => 'sometimes|boolean',
            'foto' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($produk->foto) {
                @unlink(public_path('storage/' . $produk->foto));
            }
            $file = $request->file('foto');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $destDir = public_path('storage/produk');
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }
            $file->move($destDir, $fileName);
            $data['foto'] = 'produk/' . $fileName;
        }

        $produk->update($data);
        $produk->foto_url = $produk->foto ? asset('storage/' . $produk->foto) : null;

        AuditLog::catat($request->user()->username, "Mengubah data produk \"{$produk->nama_produk}\"", $request->user()->id);

        return response()->json($produk);
    }

    public function destroy(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        // Produk yang sudah pernah ada di transaksi tidak boleh dihapus
        // total (bisa merusak riwayat pesanan lama) — cukup dinonaktifkan
        // saja supaya tidak muncul lagi di toko, tapi datanya tetap aman.
        $sudahAdaTransaksi = \App\Models\DetailTransaksi::where('produk_id', $id)->exists();

        if ($sudahAdaTransaksi) {
            $produk->update(['is_active' => false]);
            AuditLog::catat($request->user()->username, "Menonaktifkan produk \"{$produk->nama_produk}\" (SKU: {$produk->sku}) — tidak bisa dihapus total karena sudah ada di riwayat transaksi", $request->user()->id);
            return response()->json([
                'message' => 'Produk ini sudah pernah terjual (ada di riwayat transaksi), jadi tidak bisa dihapus total. Produk dinonaktifkan saja supaya tidak muncul lagi di toko.',
                'dinonaktifkan' => true,
            ]);
        }

        if ($produk->foto) {
            @unlink(public_path('storage/' . $produk->foto));
        }
        AuditLog::catat($request->user()->username, "Menghapus produk \"{$produk->nama_produk}\" (SKU: {$produk->sku})", $request->user()->id);
        $produk->delete();
        return response()->json(['message' => 'Produk dihapus']);
    }
}
