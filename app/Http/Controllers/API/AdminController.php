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
        ] ,201);
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
        'profilepicture' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
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

    // Debugging: Log the request data
    \Log::info('Request data:', $request->all());

    // Update other fields
    $user->username = $request->input('username', $user->username);
    $user->fullname = $request->input('fullname', $user->fullname);
    $user->email = $request->input('email', $user->email);
    $user->phonenumber = $request->input('phonenumber', $user->phonenumber);
    $user->alamat = $request->input('alamat', $user->alamat);
    $user->role = $request->input('role', $user->role);

    // Debugging: Log the updated user data before saving
    \Log::info('Updated user data before saving:', $user->toArray());

    $user->save();

    // Debugging: Log the updated user data after saving
    \Log::info('Updated user data after saving:', $user->toArray());

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
