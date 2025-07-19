<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KeranjangItem;
use App\Models\Atk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\KeranjangItemResource;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class KeranjangController extends Controller
{
    /**
     * Display a listing of the resource (tampilkan isi keranjang user).
     * GET /api/keranjang
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $keranjangItems = $user->keranjangItems()->with('atk.kategoriAtk')->get();
        return KeranjangItemResource::collection($keranjangItems);
    }

    /**
     * Store a newly created resource in storage (tambahkan item ke keranjang).
     * POST /api/keranjang
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $validated = $request->validate([
                'atk_id' => ['required', Rule::exists('atk', 'id')->where(fn($query) => $query->where('stok', '>', 0))],
                'quantity' => 'required|integer|min:1',
            ]);

            $atkId = $validated['atk_id'];
            $quantity = $validated['quantity'];
            $atk = Atk::find($atkId);

            if (!$atk) {
                return response()->json(['message' => 'ATK tidak ditemukan.'], 404);
            }

            if ($atk->stok < $quantity) {
                return response()->json([
                    'message' => 'Stok ATK tidak mencukupi.',
                    'stok_tersedia' => $atk->stok
                ], 422);
            }

            $keranjangItem = $user->keranjangItems()
                ->where('atk_id', $atkId)
                ->first();

            if ($keranjangItem) {
                $newQuantity = $keranjangItem->quantity + $quantity;
                if ($newQuantity > $atk->stok) {
                    return response()->json([
                        'message' => 'Stok ATK tidak cukup untuk jumlah ini.',
                        'stok_tersedia' => $atk->stok
                    ], 422);
                }
                $keranjangItem->quantity = $newQuantity;
                $keranjangItem->save();
            } else {
                $keranjangItem = $user->keranjangItems()->create([
                    'user_id' => $user->id,
                    'atk_id' => $atkId,
                    'quantity' => $quantity,
                ]);
            }

            return new KeranjangItemResource($keranjangItem->load('atk.kategoriAtk'));

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in KeranjangController@store', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in KeranjangController@store', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat menambahkan item ke keranjang.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/keranjang/{keranjangItem}
     */
    public function update(Request $request, KeranjangItem $keranjangItem)
    {
        $validated = $request->validate(['quantity' => 'required|integer|min:1']);
        $atk = $keranjangItem->atk;

        if (!$atk || $atk->stok < $validated['quantity']) {
            return response()->json(['message' => 'Stok ATK tidak mencukupi.'], 422);
        }

        $keranjangItem->update(['quantity' => $validated['quantity']]);

        return new KeranjangItemResource($keranjangItem->load('atk.kategoriAtk'));
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/keranjang/{keranjangItem}
     */
    public function destroy(KeranjangItem $keranjangItem)
    {
        $keranjangItem->delete();
        return response()->json(['message' => 'Item berhasil dihapus dari keranjang.'], 200);
    }

    /**
     * Remove all items from the user's cart.
     * DELETE /api/keranjang/clear
     */
    public function clear()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->keranjangItems()->delete();
        return response()->json(['message' => 'Keranjang berhasil dikosongkan.'], 200);
    }
}