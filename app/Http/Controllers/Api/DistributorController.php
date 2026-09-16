<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use Illuminate\Http\Request;

class DistributorController extends Controller
{
    // POST /api/distributor/register — form publik, tidak perlu login
    public function register(Request $request)
    {
        $data = $request->validate([
            'nama_perusahaan' => 'required|string|max:150',
            'nama_pic' => 'required|string|max:100',
            'no_hp' => 'required|string|max:20',
            'email' => 'required|email|max:150',
            'alamat' => 'required|string',
            'npwp' => 'nullable|string|max:30',
        ]);
        $data['status'] = 'pending';

        $distributor = Distributor::create($data);
        return response()->json($distributor, 201);
    }
}
