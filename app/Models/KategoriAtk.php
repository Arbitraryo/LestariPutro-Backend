<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nama_kategori
 * @property string $slug
 * @property string|null $deskripsi
 */
class KategoriAtk extends Model
{
    use HasFactory;

    protected $table = 'kategori_atk';

    protected $fillable = [
        'nama_kategori',
        'slug',
        'deskripsi',
    ];

    public function atk()
    {
        return $this->hasMany(Atk::class, 'kategori_atk_id');
    }
}