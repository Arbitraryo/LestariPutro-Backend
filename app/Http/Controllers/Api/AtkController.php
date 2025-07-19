<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AtkResource;
use App\Http\Resources\KategoriResource;
use App\Models\Atk;
use App\Models\KategoriAtk;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class AtkController extends Controller
{
    /**
     * Menampilkan daftar ATK dengan filter dan pencarian.
     * GET /api/atk
     */
    public function index(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
            'sort' => 'nullable|string|in:harga,created_at,nama_atk',
            'order' => 'nullable|string|in:asc,desc',
            'status_ketersediaan' => 'nullable|string|in:Tersedia,Habis',
            'kategori_slug' => 'nullable|string|exists:kategori_atk,slug'
        ]);

        $searchQuery = $request->query('q');
        $sortBy = $request->query('sort', 'created_at');
        $sortOrder = $request->query('order', 'desc');
        $statusKetersediaan = $request->query('status_ketersediaan');
        $kategoriSlug = $request->query('kategori_slug');

        $atkQuery = Atk::with('kategoriAtk');

        if ($statusKetersediaan) {
            $atkQuery->where('status_ketersediaan', $statusKetersediaan);
        }

        if ($kategoriSlug) {
            $atkQuery->whereHas('kategoriAtk', function (Builder $query) use ($kategoriSlug) {
                $query->where('slug', $kategoriSlug);
            });
        }

        if ($searchQuery) {
            $atkQuery->where(function (Builder $query) use ($searchQuery) {
                $query->where('nama_atk', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('deskripsi', 'LIKE', "%{$searchQuery}%");
            });
        }

        $allowedSorts = ['harga', 'created_at', 'nama_atk'];
        $sortField = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
        $sortDirection = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $atkQuery->orderBy($sortField, $sortDirection);

        if ($sortField !== 'nama_atk') {
            $atkQuery->orderBy('nama_atk', 'asc');
        }

        $atk = $atkQuery->paginate(12)->withQueryString();

        return AtkResource::collection($atk);
    }

    /**
     * Menampilkan detail satu ATK.
     * GET /api/atk/{atk}
     */
    public function show(Atk $atk)
    {
        $atk->loadMissing('kategoriAtk');

        return new AtkResource($atk);
    }

    /**
     * Menampilkan daftar kategori ATK.
     * GET /api/atk/kategori
     */
    public function daftarKategori()
    {
        $kategori = KategoriAtk::orderBy('nama_kategori', 'asc')->get();

        return KategoriResource::collection($kategori);
    }
}