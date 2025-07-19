<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ItemPesanan extends Model
{
    use HasFactory;

    protected $table = 'item_pesanan';

    protected $fillable = [
        'pesanan_id',
        'atk_id',
        'jumlah',
        'harga_saat_pesanan',
    ];

    /**
     * Mendefinisikan relasi bahwa setiap item pesanan merujuk ke satu produk ATK.
     */
    public function atk(): BelongsTo
    {
        return $this->belongsTo(Atk::class, 'atk_id');
    }

    /**
     * Mendefinisikan relasi bahwa setiap item pesanan adalah bagian dari satu pesanan.
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }
}