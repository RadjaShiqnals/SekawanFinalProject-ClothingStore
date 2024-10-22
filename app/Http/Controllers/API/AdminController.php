<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\KategoriPakaian;
use App\Models\MetodePembayaran;
use Illuminate\Http\Request;
use App\Models\Pakaian;

class AdminController extends Controller
{
    public function createKategoriPakaian(Request $request)
    {
        // Check if the authenticated user has the role "Pengguna"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'kategori_pakaian_nama' => 'required|string|max:255|unique:kategori_pakaian',
        ]);

        $kategoriPakaian = KategoriPakaian::create([
            'kategori_pakaian_nama' => $request->kategori_pakaian_nama,
        ]);

        return response()->json(['message' => 'Kategori Pakaian created successfully', 'data' => $kategoriPakaian], 201);
    }

    public function createMetodePembayaran(Request $request)
    {
        // Check if the authenticated user has the role "Pengguna"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'metode_pembayaran_jenis' => 'required|in:DANA,OVO,BCA,COD',
            'metode_pembayaran_nomor' => 'required|string|max:50',
        ]);

        // Check if the user already has the payment method type
        $existingMetodePembayaran = MetodePembayaran::where('metode_pembayaran_user_id', $user->id)
            ->where('metode_pembayaran_jenis', $request->metode_pembayaran_jenis)
            ->first();

        if ($existingMetodePembayaran) {
            return response()->json(['message' => 'You already have this payment method type'], 400);
        }

        $metodePembayaran = MetodePembayaran::create([
            'metode_pembayaran_user_id' => $user->id,
            'metode_pembayaran_jenis' => $request->metode_pembayaran_jenis,
            'metode_pembayaran_nomor' => $request->metode_pembayaran_nomor,
        ]);

        return response()->json(['message' => 'Metode Pembayaran created successfully', 'data' => $metodePembayaran], 201);
    }
    public function createPakaian(Request $request)
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'pakaian_kategori_pakaian_id' => 'required|exists:kategori_pakaian,kategori_pakaian_id',
            'pakaian_nama' => 'required|string|max:255',
            'pakaian_harga' => 'required|numeric|min:0',
            'pakaian_stok' => 'required|integer|min:0',
            'pakaian_gambar_url' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);
    
        $pakaianGambarUrl = 'pakaian_default.jpg';
        if ($request->hasFile('pakaian_gambar_url')) {
            $file = $request->file('pakaian_gambar_url');
            $storedPath = $file->store('pakaian', 'public');
            $pakaianGambarUrl = basename($storedPath);
        }
    
        $pakaian = Pakaian::create([
            'pakaian_kategori_pakaian_id' => $request->pakaian_kategori_pakaian_id,
            'pakaian_nama' => $request->pakaian_nama,
            'pakaian_harga' => $request->pakaian_harga,
            'pakaian_stok' => $request->pakaian_stok,
            'pakaian_gambar_url' => $pakaianGambarUrl,
        ]);

        return response()->json(['message' => 'Pakaian created successfully', 'data' => $pakaian], 201);
    }
}
