<?php

namespace Database\Factories;

use App\Models\KategoriAtk;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KategoriAtkFactory extends Factory
{
    protected $model = KategoriAtk::class;

    public function definition(): array
    {
        $namaKategori = $this->faker->randomElement([
            'Pulpen dan Pensil',
            'Kertas dan Buku',
            'Alat Tulis Kantor',
            'Peralatan Presentasi',
            'Alat Gambar',
            'Peralatan Arsip',
            'Alat Ukur',
            'Peralatan Komputer',
            'Alat Potong',
            'Peralatan Penjilidan'
        ]);

        return [
            'nama_kategori' => $namaKategori,
            'slug' => Str::slug($namaKategori),
            'deskripsi' => $this->faker->paragraph(3),
        ];
    }
}