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

Route::post('/admin/kategori-pakaian', [AdminController::class, 'createKategoriPakaian']);
Route::post('/admin/create-pakaian', [AdminController::class, 'createPakaian']);
Route::post('/admin/create-user', [AdminController::class, 'createUser']);
Route::post('/admin/update-user/{id}', [AdminController::class, 'updateUser']);
Route::delete('/admin/delete-user/{id}', [AdminController::class, 'deleteUser']);
Route::get('/admin/get-transaksi', [AdminController::class, 'getAllTransactions']);
Route::get('/admin/get-pakaian', [AdminController::class, 'getAllPakaian']);
Route::get('/admin/get-category', [AdminController::class, 'getAllKategoriPakaian']);
Route::post('/admin/create-metode-pembayaran', [AdminController::class, 'createMetodePembayaran']);
Route::get('/admin/get-user', [AdminController::class, 'getAllUsers']);

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