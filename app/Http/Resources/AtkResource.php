<?php
// File: app/Http/Resources/Api/AtkResource.php

namespace App\Http\Resources;

use App\Http\Resources\KategoriResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AtkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Pastikan relasi 'kategoriAtk' dimuat saat resource ini digunakan
        // Misalnya: Atk::with('kategoriAtk')->find($id);

        return [
            'id' => $this->id,
            'nama_atk' => $this->nama_atk, // Mengacu pada kolom 'nama_atk'
            'slug' => $this->slug,
            'deskripsi' => $this->deskripsi,
            'harga' => (float) $this->harga, // Menggunakan float agar nilai koma tetap terjaga
            'stok' => (int) $this->stok,
            'status_ketersediaan' => $this->status_ketersediaan,

            // Kolom gambar_utama akan mengembalikan URL Cloudinary yang disimpan di database
            // Asumsi: Model Atk sudah menyimpan URL lengkap dari Cloudinary di atribut ini.
            // Jika ada accessor di model (misal: getGambarUtamaUrlAttribute), gunakan $this->gambar_utama_url
            'gambar_utama' => $this->gambar_utama,
            'gambar_utama_url' => $this->gambar_utama_url,

            // Relasi ke KategoriAtkResource (pastikan KategoriResource sudah diupdate/disesuaikan)
            'kategori' => KategoriResource::make($this->whenLoaded('kategoriAtk')), // Mengacu pada relasi kategoriAtk

            'dibuat_pada' => $this->created_at->format('Y-m-d H:i:s'), // Format tanggal dan waktu
            'diupdate_pada' => $this->updated_at->format('Y-m-d H:i:s'), // Format tanggal dan waktu
        ];
    }
}