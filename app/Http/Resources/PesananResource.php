<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PesananResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user'),
            'nama_pelanggan' => $this->nama_pelanggan,
            'nomor_whatsapp' => $this->nomor_whatsapp,
            'alamat_pengiriman' => $this->alamat_pengiriman,
            'total_harga' => $this->total_harga,
            'metode_pembayaran' => $this->metode_pembayaran,
            'status_pembayaran' => $this->status_pembayaran,
            'status' => $this->status,
            'nomor_resi' => $this->nomor_resi,
            'tanggal_pesanan' => $this->tanggal_pesanan,
            'catatan' => $this->catatan,
            'catatan_admin' => $this->catatan_admin,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    $atk = $item->atk;

                    if (!$atk) {
                        return [
                            'id' => $item->id,
                            'nama_atk' => 'Produk Tidak Ditemukan',
                            'gambar_utama_url' => null,
                            'jumlah' => $item->jumlah,
                            'harga_saat_pesanan' => $item->harga_saat_pesanan,
                            'is_deleted' => true,
                        ];
                    }

                    return [
                        'id' => $item->id,
                        'nama_atk' => $atk->nama_atk,
                        'slug' => $atk->slug,
                        'deskripsi' => $atk->deskripsi,
                        'harga' => $atk->harga,
                        'stok' => $atk->stok,
                        'kategori_atk' => $atk->kategoriAtk,
                        'gambar_utama_url' => $atk->gambar_utama_url,
                        'jumlah' => $item->jumlah,
                        'harga_saat_pesanan' => $item->harga_saat_pesanan,
                    ];
                });
            }),
        ];
    }
}