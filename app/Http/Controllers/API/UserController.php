<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Pakaian;
use App\Models\MetodePembayaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function createMetodePembayaran(Request $request)
    {
        // Check if the authenticated user has the role "Pengguna"
        $user = auth('api')->user();
        if ($user->role !== 'Pengguna') {
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

    public function buyClothes(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.pakaian_id' => 'required|exists:pakaian,pakaian_id',
            'items.*.quantity' => 'required|integer|min:1',
            'metode_pembayaran_id' => 'required|exists:metode_pembayaran,metode_pembayaran_id',
        ]);

        $user = Auth::user();
        $metodePembayaranId = $request->metode_pembayaran_id;
        $items = $request->items;

        DB::transaction(function () use ($user, $metodePembayaranId, $items) {
            $totalHarga = 0;

            foreach ($items as $item) {
                $pakaian = Pakaian::find($item['pakaian_id']);
                $totalHarga += $pakaian->pakaian_harga * $item['quantity'];
            }

            $pembelian = Pembelian::create([
                'pembelian_user_id' => $user->id,
                'pembelian_metode_pembayaran_id' => $metodePembayaranId,
                'pembelian_tanggal' => now(),
                'pembelian_total_harga' => $totalHarga,
            ]);

            foreach ($items as $item) {
                PembelianDetail::create([
                    'pembelian_detail_pembelian_id' => $pembelian->pembelian_id,
                    'pembelian_detail_pakaian_id' => $item['pakaian_id'],
                    'pembelian_detail_jumlah' => $item['quantity'],
                    'pembelian_detail_total_harga' => Pakaian::find($item['pakaian_id'])->pakaian_harga * $item['quantity'],
                ]);
            }
        });

        return response()->json(['message' => 'Purchase successful'], 200);
    }
}
