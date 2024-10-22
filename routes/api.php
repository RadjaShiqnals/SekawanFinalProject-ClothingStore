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

Route::post('/user/metode-pembayaran', [UserController::class, 'createMetodePembayaran']);
Route::post('user/register', [AuthController::class, 'register']);
Route::post('user/login', [AuthController::class, 'login']);
Route::post('user/logout', [AuthController::class, 'logout']);