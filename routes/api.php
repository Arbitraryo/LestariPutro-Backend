<?php

// File: routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- IMPORTS CONTROLLER API ---
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AtkController;
use App\Http\Controllers\Api\PesananApiController;
use App\Http\Controllers\Api\KeranjangController;
use App\Http\Controllers\Api\PaymentProofController;
use App\Http\Controllers\Api\UserProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// == API Endpoints Katalog Produk (ATK) - Publik ==
// Endpoint untuk mendapatkan daftar kategori ATK
Route::get('/kategori', [AtkController::class, 'daftarKategori'])->name('api.kategori.index');

// Endpoint untuk mendapatkan daftar ATK
Route::get('/atk', [AtkController::class, 'index'])->name('api.atk.index');
// Endpoint untuk mendapatkan informasi ATK berdasarkan slug
Route::get('/atk/{atk:slug}', [AtkController::class, 'show'])->name('api.atk.show');


// == API Endpoints Otentikasi - Publik ==
// Endpoint untuk registrasi pengguna baru
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
// Endpoint untuk login dan mendapatkan token autentikasi
Route::post('/login', [AuthController::class, 'login'])->name('api.login');


// == API Endpoints yang Memerlukan Otentikasi (Sanctum Token) ==
// Semua endpoint yang memerlukan otentikasi berada di dalam grup ini
Route::middleware('auth:api')->group(function () {

    // --- Autentikasi & Profil Pengguna ---
    // Endpoint untuk melakukan logout dan menghapus token
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    // Endpoint untuk mendapatkan data pengguna yang sedang login
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');
    // Endpoint untuk update foto profil pengguna
    Route::post('/user/profile-photo', [UserProfileController::class, 'updateProfilePhoto'])->name('user.photo.update');
    // Tambahkan route untuk menghapus foto jika diperlukan
    // Route::delete('/user/profile-photo', [UserProfileController::class, 'deleteProfilePhoto'])->name('user.photo.delete');


    // --- Manajemen Pesanan ---
    // Route resource API untuk pesanan (index, show, store, update, destroy)
    Route::apiResource('pesanan', PesananApiController::class);
    // Route kustom untuk pesanan
    Route::post('/pesanan/{pesanan}/submit-payment-proof', [PaymentProofController::class, 'submitProof'])
        ->name('api.pesanan.submitProof');
    Route::put('/pesanan/{pesanan}/tandai-selesai', [PesananApiController::class, 'tandaiSelesai'])->name('api.pesanan.tandaiSelesai');


    // --- Manajemen Keranjang Belanja ---
    // Route resource API untuk keranjang belanja (index, store, update, destroy)
    Route::apiResource('keranjang', KeranjangController::class)->except(['show']);
    Route::delete('/keranjang/clear', [KeranjangController::class, 'clear'])->name('keranjang.clear');

});


// Route fallback jika endpoint API tidak ditemukan (opsional)
// Jika endpoint yang diminta tidak ada, akan memberikan respons error 404
Route::fallback(function () {
    return response()->json(['message' => 'Endpoint tidak ditemukan.'], 404);
});

