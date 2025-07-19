<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

/**
 * @property int $id
 * @property int $kategori_atk_id
 * @property string $nama_atk
 * @property string $slug
 * @property string|null $deskripsi
 * @property float $harga
 * @property int $stok
 * @property string $status_ketersediaan
 * @property string|null $gambar_utama
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\KeranjangItem[] $keranjangItems
 */
class Atk extends Model
{
    use HasFactory;

    protected $table = 'atk';

    protected $fillable = [
        'kategori_atk_id',
        'nama_atk',
        'slug',
        'deskripsi',
        'harga',
        'stok',
        'status_ketersediaan',
        'gambar_utama',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok' => 'integer',
    ];

    // Relasi ke KategoriAtk
    public function kategoriAtk(): BelongsTo
    {
        return $this->belongsTo(KategoriAtk::class, 'kategori_atk_id');
    }

    // Relasi untuk Pesanan (Many-to-Many) melalui tabel pivot 'item_pesanan'
    public function pesanan(): BelongsToMany
    {
        return $this->belongsToMany(Pesanan::class, 'item_pesanan', 'atk_id', 'pesanan_id')
            ->withPivot('jumlah', 'harga_saat_pesanan')
            ->withTimestamps();
    }

    // Relasi untuk Keranjang
    public function keranjangItems(): HasMany
    {
        return $this->hasMany(KeranjangItem::class, 'atk_id');
    }

    // Accessor untuk mendapatkan URL gambar utama yang sudah di-transformasi oleh Cloudinary
    public function getGambarUtamaUrlAttribute(): ?string
    {
        if ($this->gambar_utama) {
            if (Str::startsWith($this->gambar_utama, ['http://', 'https://'])) {
                return $this->gambar_utama;
            }
            $cloudName = env('CLOUDINARY_CLOUD_NAME');
            return "https://res.cloudinary.com/{$cloudName}/image/upload/{$this->gambar_utama}";
        }
        return null;
    }
}