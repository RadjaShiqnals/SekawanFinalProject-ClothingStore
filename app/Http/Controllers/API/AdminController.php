<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\KategoriPakaian;
use App\Models\MetodePembayaran;
use Illuminate\Http\Request;
use App\Models\Pakaian;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Pembelian;
use App\Models\PembelianDetail;

class AdminController extends Controller
{
    public function createUser(Request $request)
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'fullname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phonenumber' => 'required|string|max:13|min:11',
            'alamat' => 'required|string|max:255',
            'profilepicture' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'role' => 'required|in:Pengguna,Admin',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $profilePicturePath = 'default.jpg';
        if ($request->hasFile('profilepicture')) {
            $file = $request->file('profilepicture');
            $storedPath = $file->store('profilepictures', 'public');
            $profilePicturePath = basename($storedPath);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'fullname' => $request->fullname,
            'email' => $request->email,
            'phonenumber' => $request->phonenumber,
            'alamat' => $request->alamat,
            'profilepicture' => $profilePicturePath,
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);
    }

    public function updateUser(Request $request, $id)
    {
        // Check if the authenticated user has the role "Admin"
        $authUser = auth('api')->user();
        if ($authUser->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $id,
            'password' => 'sometimes|required|string|min:6|confirmed',
            'fullname' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'phonenumber' => 'sometimes|required|string|max:13|min:11',
            'alamat' => 'sometimes|required|string|max:255',
            'profilepicture' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
            'role' => 'sometimes|required|in:Pengguna,Admin',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::findOrFail($id);

        if ($request->hasFile('profilepicture')) {
            // Get the old profile picture
            $oldProfilePicture = $user->profilepicture;

            // Store the new profile picture
            $file = $request->file('profilepicture');
            $storedPath = $file->store('profilepictures', 'public');
            $user->profilepicture = basename($storedPath);

            // Delete the old profile picture if it's not the default one
            if ($oldProfilePicture !== 'default.jpg') {
                Storage::disk('public')->delete('profilepictures/' . $oldProfilePicture);
            }
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Update other fields
        $user->username = $request->input('username', $user->username);
        $user->fullname = $request->input('fullname', $user->fullname);
        $user->email = $request->input('email', $user->email);
        $user->phonenumber = $request->input('phonenumber', $user->phonenumber);
        $user->alamat = $request->input('alamat', $user->alamat);
        $user->role = $request->input('role', $user->role);

        $user->save();

        return response()->json(['message' => 'User updated successfully', 'data' => $user], 200);
    }

    public function deleteUser($id)
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function getAllUsers()
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::all();

        return response()->json(['message' => 'Users retrieved successfully', 'data' => $users], 200);
    }

    public function getUser($id)
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $selectedUser = User::find($id);

        if (!$selectedUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['message' => 'User retrieved successfully', 'data' => $selectedUser], 200);
    }

    public function createKategoriPakaian(Request $request)
    {
        // Check if the authenticated user has the role "Admin"
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
            'metode_pembayaran_user_id' => 'required|numeric|min:11|max:13',
            'metode_pembayaran_jenis' => 'required|in:DANA,OVO,BCA,COD',
            'metode_pembayaran_nomor' => 'required|string|max:50',
        ]);

        // Check if the user already has the payment method type
        $existingMetodePembayaran = MetodePembayaran::where('metode_pembayaran_user_id', $request->metode_pembayaran_user_id)
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

    public function updatePakaian(Request $request, $id)
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'pakaian_kategori_pakaian_id' => 'sometimes|required|exists:kategori_pakaian,kategori_pakaian_id',
            'pakaian_nama' => 'sometimes|required|string|max:255',
            'pakaian_harga' => 'sometimes|required|numeric|min:0',
            'pakaian_stok' => 'sometimes|required|integer|min:0',
            'pakaian_gambar_url' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pakaian = Pakaian::findOrFail($id);

        if ($request->hasFile('pakaian_gambar_url')) {
            // Get the old image
            $oldImage = $pakaian->pakaian_gambar_url;

            // Store the new image
            $file = $request->file('pakaian_gambar_url');
            $storedPath = $file->store('pakaian', 'public');
            $pakaian->pakaian_gambar_url = basename($storedPath);

            // Delete the old image if it's not the default one
            if ($oldImage !== 'pakaian_default.jpg') {
                Storage::disk('public')->delete('pakaian/' . $oldImage);
            }
        }

        // Update other fields
        $pakaian->pakaian_kategori_pakaian_id = $request->input('pakaian_kategori_pakaian_id', $pakaian->pakaian_kategori_pakaian_id);
        $pakaian->pakaian_nama = $request->input('pakaian_nama', $pakaian->pakaian_nama);
        $pakaian->pakaian_harga = $request->input('pakaian_harga', $pakaian->pakaian_harga);
        $pakaian->pakaian_stok = $request->input('pakaian_stok', $pakaian->pakaian_stok);

        $pakaian->save();

        return response()->json(['message' => 'Pakaian updated successfully', 'data' => $pakaian], 200);
    }
    public function updateMetodePembayaran(Request $request, $id)
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'metode_pembayaran_user_id' => 'sometimes|required|numeric|min:11|max:13',
            'metode_pembayaran_jenis' => 'sometimes|required|in:DANA,OVO,BCA,COD',
            'metode_pembayaran_nomor' => 'sometimes|required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $metodePembayaran = MetodePembayaran::findOrFail($id);

        // Update fields
        $metodePembayaran->metode_pembayaran_user_id = $request->input('metode_pembayaran_user_id', $metodePembayaran->metode_pembayaran_user_id);
        $metodePembayaran->metode_pembayaran_jenis = $request->input('metode_pembayaran_jenis', $metodePembayaran->metode_pembayaran_jenis);
        $metodePembayaran->metode_pembayaran_nomor = $request->input('metode_pembayaran_nomor', $metodePembayaran->metode_pembayaran_nomor);

        $metodePembayaran->save();

        return response()->json(['message' => 'Metode Pembayaran updated successfully', 'data' => $metodePembayaran], 200);
    }
    public function updateKategoriPakaian(Request $request, $id)
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'kategori_pakaian_nama' => 'sometimes|required|string|max:255|unique:kategori_pakaian,kategori_pakaian_nama,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kategoriPakaian = KategoriPakaian::findOrFail($id);

        // Update fields
        $kategoriPakaian->kategori_pakaian_nama = $request->input('kategori_pakaian_nama', $kategoriPakaian->kategori_pakaian_nama);

        $kategoriPakaian->save();

        return response()->json(['message' => 'Kategori Pakaian updated successfully', 'data' => $kategoriPakaian], 200);
    }
    public function deletePakaian($id)
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pakaian = Pakaian::findOrFail($id);
        $pakaian->delete();

        return response()->json(['message' => 'Pakaian deleted successfully'], 200);
    }
    public function deleteMetodePembayaran($id)
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $metodePembayaran = MetodePembayaran::findOrFail($id);
        $metodePembayaran->delete();

        return response()->json(['message' => 'Metode Pembayaran deleted successfully'], 200);
    }
    public function deleteKategoriPakaian($id)
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $kategoriPakaian = KategoriPakaian::findOrFail($id);
        $kategoriPakaian->delete();

        return response()->json(['message' => 'Kategori Pakaian deleted successfully'], 200);
    }
    public function getPakaianDetailsByPembelianId($pembelianId)
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Retrieve the pakaian details based on the pembelian ID
        $pakaianDetails = PembelianDetail::with('pakaian')
            ->where('pembelian_detail_pembelian_id', $pembelianId)
            ->get();

        if ($pakaianDetails->isEmpty()) {
            return response()->json(['message' => 'No pakaian details found for the given pembelian ID'], 404);
        }

        return response()->json(['message' => 'Pakaian details retrieved successfully', 'data' => $pakaianDetails], 200);
    }

    public function deleteTransaction($transactionId)
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Find the transaction
        $transaction = Pembelian::find($transactionId);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Soft delete the transaction
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully'], 200);
    }

    public function getAllTransactions()
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $transactions = Pembelian::with(['user', 'metodePembayaran'])->get();

        return response()->json(['message' => 'Transactions retrieved successfully', 'data' => $transactions], 200);
    }

    public function getAllPakaian()
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pakaian = Pakaian::with('kategoriPakaian')->get();

        return response()->json(['message' => 'Pakaian retrieved successfully', 'data' => $pakaian], 200);
    }

    public function getAllKategoriPakaian()
    {
        // Check if the authenticated user has the role "Admin"
        $user = auth('api')->user();
        if ($user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $kategoriPakaian = KategoriPakaian::with('pakaian')->get();

        return response()->json(['message' => 'Kategori Pakaian retrieved successfully', 'data' => $kategoriPakaian], 200);
    }
}
