<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user->role === 'Admin') {
        return Inertia::render('Admin/AdminDashboard');
    } elseif ($user->role === 'Pengguna') {
        return Inertia::render('User/UserDashboard');
    } else {
        abort(403, 'Unauthorized');
    }
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard/transaksi', function () {
        $user = Auth::user();
        if ($user->role === 'Pengguna') {
            return Inertia::render('User/UserTransactions');
        } else {
            abort(403, 'Unauthorized');
        }
    })->name('user.transaksi');
    Route::get('/dashboard/clothes', function () {
        $user = Auth::user();
        if ($user->role === 'Admin') {
            return Inertia::render('Admin/AdminClothes');
        } else {
            abort(403, 'Unauthorized');
        }
    })->name('admin.clothes');
    Route::get('/dashboard/user', function () {
        $user = Auth::user();
        if ($user->role === 'Admin') {
            return Inertia::render('Admin/AdminUser');
        } else {
            abort(403, 'Unauthorized');
        }
    })->name('admin.user');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
