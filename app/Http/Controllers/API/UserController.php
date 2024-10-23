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
use Illuminate\Support\Facades\Validator;

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

    public function editMetodePembayaran(Request $request, $id)
    {
        // Check if the authenticated user has the role "Pengguna"
        $user = auth('api')->user();
        if ($user->role !== 'Pengguna') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'metode_pembayaran_nomor' => 'required|string|min:11|max:13',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        $metodePembayaran = MetodePembayaran::where('metode_pembayaran_user_id', $user->id)
            ->where('metode_pembayaran_id', $id)
            ->first();

        if (!$metodePembayaran) {
            return response()->json(['message' => 'Payment method not found'], 404);
        }

        $metodePembayaran->update([
            'metode_pembayaran_nomor' => $request->metode_pembayaran_nomor,
        ]);

        return response()->json(['message' => 'Metode Pembayaran updated successfully', 'data' => $metodePembayaran], 200);
    }

    public function addItemToNewCart(Request $request, $pembelianId = null)
    {
        // Check if the authenticated user has the role "Pengguna"
        $user = auth('api')->user();
        if ($user->role !== 'Pengguna') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.pakaian_id' => 'required|exists:pakaian,pakaian_id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $items = $request->items;

        // Create a new Pembelian
        $pembelian = Pembelian::create([
            'pembelian_user_id' => $user->id,
            'pembelian_metode_pembayaran_id' => null,
            'pembelian_tanggal' => now(),
            'pembelian_total_harga' => 0,
            'status' => 'belum_bayar',
        ]);

        $totalHarga = 0;

        foreach ($items as $item) {
            $pakaianId = $item['pakaian_id'];
            $quantity = $item['quantity'];

            $pakaian = Pakaian::find($pakaianId);

            // Check if the stock is sufficient
            if ($pakaian->pakaian_stok < $quantity) {
                return response()->json(['message' => 'Item is out of stock'], 400);
            }

            $itemTotalHarga = $pakaian->pakaian_harga * $quantity;
            $totalHarga += $itemTotalHarga;

            // Create PembelianDetail entry
            PembelianDetail::create([
                'pembelian_detail_pembelian_id' => $pembelian->pembelian_id,
                'pembelian_detail_pakaian_id' => $pakaianId,
                'pembelian_detail_jumlah' => $quantity,
                'pembelian_detail_total_harga' => $itemTotalHarga,
            ]);

            // Update Pakaian stock
            $pakaian->update([
                'pakaian_stok' => $pakaian->pakaian_stok - $quantity,
            ]);
        }

        // Update the pembelian_total_harga to include the new items' total price
        $pembelian->update([
            'pembelian_total_harga' => $totalHarga,
        ]);

        return response()->json(['message' => 'Items added to cart successfully', 'data' => $pembelian], 200);
    }

    public function addItemToSelectedCart(Request $request)
{
    // Check if the authenticated user has the role "Pengguna"
    $user = auth('api')->user();
    if ($user->role !== 'Pengguna') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $request->validate([
        'pembelian_id' => 'required|exists:pembelian,pembelian_id',
        'items' => 'required|array',
        'items.*.pakaian_id' => 'required|exists:pakaian,pakaian_id',
        'items.*.quantity' => 'required|integer|min:1',
    ]);

    $pembelianId = $request->pembelian_id;
    $items = $request->items;

    // Retrieve the Pembelian
    $pembelian = Pembelian::where('pembelian_id', $pembelianId)
        ->where('pembelian_user_id', $user->id)
        ->where('status', 'belum_bayar')
        ->first();

    if (!$pembelian) {
        return response()->json(['message' => 'Cart not found or already paid'], 404);
    }

    $totalHarga = 0;

    foreach ($items as $item) {
        $pakaianId = $item['pakaian_id'];
        $quantity = $item['quantity'];

        // Retrieve the Pakaian
        $pakaian = Pakaian::find($pakaianId);

        // Check if the stock is sufficient
        if ($pakaian->pakaian_stok < $quantity) {
            return response()->json(['message' => 'Item is out of stock'], 400);
        }

        $itemTotalHarga = $pakaian->pakaian_harga * $quantity;
        $totalHarga += $itemTotalHarga;

        // Create PembelianDetail entry
        PembelianDetail::create([
            'pembelian_detail_pembelian_id' => $pembelian->pembelian_id,
            'pembelian_detail_pakaian_id' => $pakaianId,
            'pembelian_detail_jumlah' => $quantity,
            'pembelian_detail_total_harga' => $itemTotalHarga,
        ]);

        // Update Pakaian stock
        $pakaian->update([
            'pakaian_stok' => $pakaian->pakaian_stok - $quantity,
        ]);
    }

    // Update the pembelian_total_harga to include the new items' total price
    $pembelian->update([
        'pembelian_total_harga' => $pembelian->pembelian_total_harga + $totalHarga,
    ]);

    return response()->json(['message' => 'Items added to cart successfully', 'data' => $pembelian, 'stock' => $pakaian], 200);
}

public function payPembelian(Request $request)
{
    // Check if the authenticated user has the role "Pengguna"
    $user = auth('api')->user();
    if ($user->role !== 'Pengguna') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    
    $request->validate([
        'pembelian_id' => 'required|exists:pembelian,pembelian_id',
        'metode_pembayaran_id' => 'required|exists:metode_pembayaran,metode_pembayaran_id',
    ]);

    $pembelianId = $request->pembelian_id;
    $metodePembayaranId = $request->metode_pembayaran_id;

    // Retrieve the Pembelian
    $pembelian = Pembelian::where('pembelian_id', $pembelianId)
        ->where('pembelian_user_id', $user->id)
        ->where('status', 'belum_bayar')
        ->first();

    if (!$pembelian) {
        return response()->json(['message' => 'Cart not found or already paid'], 404);
    }

    // Retrieve the Metode Pembayaran
    $metodePembayaran = MetodePembayaran::where('metode_pembayaran_id', $metodePembayaranId)
        ->where('metode_pembayaran_user_id', $user->id)
        ->first();

    if (!$metodePembayaran) {
        return response()->json(['message' => 'Payment method not found or does not belong to the user'], 404);
    }

    // Update the Pembelian
    $pembelian->update([
        'pembelian_metode_pembayaran_id' => $metodePembayaranId,
        'status' => 'lunas',
    ]);

    return response()->json(['message' => 'Payment successful', 'data' => $pembelian], 200);
}

    public function getMyPurchases()
    {
        $user = Auth::user();
        $pembelian = Pembelian::where('pembelian_user_id', $user->id)->get();

        return response()->json(['data' => $pembelian], 200);
    }
}
