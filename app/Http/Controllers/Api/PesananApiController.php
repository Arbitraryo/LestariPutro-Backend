<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePesananApiRequest;
use App\Http\Resources\PesananResource;
use App\Models\Pesanan;
use App\Models\User; // Pastikan ini diimpor jika digunakan
use App\Services\PesananService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class PesananApiController extends Controller
{
    /**
     * Menampilkan daftar pesanan milik pengguna yang sedang login (dengan paginasi).
     * GET /api/pesanan
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // PERBAIKAN: Eager loading yang benar:
            // 'items.atk' akan memuat item pesanan, lalu atk yang terhubung
            // dengan item tersebut, dan kemudian kategori atk yang terhubung dengan atk.
            $pesanans = Pesanan::where('user_id', $user->id)
                ->with(['user', 'items.atk.kategoriAtk'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->query('per_page', 10));

            return PesananResource::collection($pesanans)->response();
        } catch (Exception $e) {
            Log::error('API Pesanan Index Error: ' . $e->getMessage(), ['exception_class' => get_class($e), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal memuat riwayat pesanan.',
                'error' => 'Terjadi kesalahan pada server.'
            ], 500);
        }
    }

    /**
     * Menyimpan pesanan baru.
     * POST /api/pesanan
     */
    public function store(StorePesananApiRequest $request, PesananService $pesananService): JsonResponse
    {
        $validatedData = $request->validated();
        try {
            $user = Auth::user();
            $pesanan = $pesananService->createOrder($validatedData, $user);

            // Clear the cart after successful order creation
            $user->keranjangItems()->delete();

            // PERBAIKAN: Eager loading yang benar setelah pesanan dibuat
            $pesanan->load(['user', 'items.atk.kategoriAtk']);

            return (new PesananResource($pesanan))
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            Log::error('API Pesanan Store Error: ' . $e->getMessage(), ['exception_class' => get_class($e), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal membuat pesanan.',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Menampilkan detail satu pesanan.
     * GET /api/pesanan/{pesanan}
     */
    public function show(Request $request, Pesanan $pesanan): JsonResponse
    {
        if ($request->user()->id !== $pesanan->user_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        try {
            // PERBAIKAN: Eager loading yang benar
            $pesanan->load(['user', 'items.atk.kategoriAtk']);
            return (new PesananResource($pesanan))->response();
        } catch (Exception $e) {
            Log::error("API Pesanan Show Error untuk pesanan #{$pesanan->id}: " . $e->getMessage(), ['exception_class' => get_class($e), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal memuat detail pesanan.',
                'error' => 'Terjadi kesalahan pada server.'
            ], 500);
        }
    }

    /**
     * Mengupdate data pesanan.
     * PUT/PATCH /api/pesanan/{pesanan}
     */
    public function update(Request $request, Pesanan $pesanan, PesananService $pesananService): JsonResponse
    {
        if ($request->user()->id !== $pesanan->user_id) {
            return response()->json(['message' => 'Akses ditolak untuk mengupdate pesanan ini.'], 403);
        }

        $validatedData = $request->validate([
            'status' => 'sometimes|string|in:dibatalkan_pelanggan',
            'catatan' => 'sometimes|nullable|string',
        ]);

        if (empty($validatedData)) {
            return response()->json(['message' => 'Tidak ada data valid untuk diupdate.'], 400);
        }

        try {
            $updatedPesanan = $pesananService->updateOrder($pesanan, $validatedData);
            // PERBAIKAN: Eager loading yang benar setelah update
            $updatedPesanan->load(['user', 'items.atk.kategoriAtk']);

            return (new PesananResource($updatedPesanan))->response();
        } catch (Exception $e) {
            Log::error("API Pesanan Update Error untuk pesanan #{$pesanan->id}: " . $e->getMessage(), ['exception_class' => get_class($e), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal mengupdate pesanan.',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Menghapus data pesanan.
     * DELETE /api/pesanan/{pesanan}
     */
    public function destroy(Request $request, Pesanan $pesanan): JsonResponse
    {
        if ($request->user()->id !== $pesanan->user_id) {
            return response()->json(['message' => 'Akses ditolak untuk menghapus pesanan ini.'], 403);
        }
        try {
            $isDeleted = $pesanan->delete();
            if ($isDeleted) {
                return response()->json(['message' => 'Pesanan berhasil dihapus.'], 200);
            } else {
                throw new Exception("Gagal menghapus pesanan dari database.");
            }
        } catch (Exception $e) {
            Log::error("API Pesanan Destroy Error untuk pesanan #{$pesanan->id}: " . $e->getMessage(), ['exception_class' => get_class($e), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal menghapus pesanan.',
                'error' => 'Terjadi kesalahan pada server.'
            ], 500);
        }
    }

    /**
     * Mengubah status pesanan menjadi 'selesai' dari sisi pelanggan.
     * POST /api/pesanan/{pesanan}/tandai-selesai
     */
    public function tandaiSelesai(Request $request, Pesanan $pesanan): JsonResponse
    {
        if ($request->user()->id !== $pesanan->user_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        if ($pesanan->status !== 'dikirim') {
            return response()->json(['message' => 'Pesanan ini tidak bisa ditandai selesai dari status saat ini.'], 422);
        }
        try {
            $pesanan->status = 'selesai';
            $pesanan->save();
            // PERBAIKAN: Eager loading yang benar setelah status diubah
            $pesanan->load(['user', 'items.atk.kategoriAtk']);
            return (new PesananResource($pesanan))->response()->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error("Gagal menandai pesanan #{$pesanan->id} selesai: " . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui status pesanan.', 'error' => 'Terjadi kesalahan server.'], 500);
        }
    }
}