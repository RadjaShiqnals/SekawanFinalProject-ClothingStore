<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AuthController;

Route::get('/user', function (Request $request): mixed {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/buy-clothes', [UserController::class, 'buyClothes']);

Route::post('/admin/create-kategori-pakaian', [AdminController::class, 'createKategoriPakaian']);
Route::post('/admin/create-pakaian', [AdminController::class, 'createPakaian']);
Route::post('/admin/create-user', [AdminController::class, 'createUser']);
Route::post('/admin/update-user/{id}', [AdminController::class, 'updateUser']);
Route::delete('/admin/delete-user/{id}', [AdminController::class, 'deleteUser']);
Route::get('/admin/get-transaksi', [AdminController::class, 'getAllTransactions']);
Route::get('/admin/pakaian-details/{pembelianId}', [AdminController::class, 'getPakaianDetailsByPembelianId']);
Route::delete('/admin/delete-transactions/{transactionId}', [AdminController::class, 'deleteTransaction']);
Route::get('/admin/get-pakaian', [AdminController::class, 'getAllPakaian']);
Route::get('/admin/get-category', [AdminController::class, 'getAllKategoriPakaian']);
Route::post('/admin/create-metode-pembayaran', [AdminController::class, 'createMetodePembayaran']);
Route::post('/admin/update-pakaian/{id}', [AdminController::class, 'updatePakaian']);
Route::post('/admin/update-metode-pembayaran/{id}', [AdminController::class, 'updateMetodePembayaran']);
Route::post('/admin/update-kategori-pakaian/{id}', [AdminController::class, 'updateKategoriPakaian']);
Route::delete('/admin/delete-pakaian/{id}', [AdminController::class, 'deletePakaian']);
Route::delete('/admin/delete-metode-pembayaran/{id}', [AdminController::class, 'deleteMetodePembayaran']);
Route::delete('/admin/delete-kategori-pakaian/{id}', [AdminController::class, 'deleteKategoriPakaian']);
Route::get('/admin/get-user', [AdminController::class, 'getAllUsers']);
Route::get('/admin/get-user/{id}', [AdminController::class, 'getUser']);

Route::post('/user/edit-metode-pembayaran/{id}', [UserController::class, 'editMetodePembayaran']);
Route::post('/user/create-metode-pembayaran', [UserController::class, 'createMetodePembayaran']);
Route::get('/user/get-metode-pembayaran', [UserController::class, 'getUserMetodePembayaran']);
Route::post('/user/add-new-item', [UserController::class, 'addItemToNewCart']);
Route::post('/user/add-item', [UserController::class, 'addItemToSelectedCart']);
Route::get('/user/get-transaksi', [UserController::class, 'getMyPurchases']);
Route::get('/user/get-detail-transaksi/{id}', [UserController::class, 'getTransactionDetails']);
Route::get('/user/get-pakaian', [UserController::class, 'getPakaian']);
Route::post('/user/pay-cart', [UserController::class, 'payPembelian']);
Route::post('user/register', [AuthController::class, 'register']);
Route::post('user/login', [AuthController::class, 'login']);
Route::post('user/logout', [AuthController::class, 'logout']);