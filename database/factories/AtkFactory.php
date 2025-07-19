<?php

namespace Database\Factories;

use App\Models\KategoriAtk;
use App\Models\Atk;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AtkFactory extends Factory
{
    protected $model = Atk::class;

    public function definition(): array
    {
        $kategoriAtk = KategoriAtk::inRandomOrder()->first();
        if (!$kategoriAtk) {
            $kategoriAtk = KategoriAtk::factory()->create();
            Log::info("Created a new KategoriAtk for seeding purposes.");
        }
        $kategoriAtkId = $kategoriAtk->id;

        // Nama ATK yang realistis
        $namaAtk = $this->generateNamaAtk();

        // Deskripsi ATK yang panjang dan relevan
        $deskripsi = $this->generateDeskripsiAtk();

        // Gambar ATK yang relevan - menggunakan Unsplash dengan keyword office supplies
        $atkImages = [
            'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=640&h=480&fit=crop',
            'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=640&h=480&fit=crop',
            'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=640&h=480&fit=crop',
            'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=640&h=480&fit=crop',
            'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=640&h=480&fit=crop',
            'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=640&h=480&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=640&h=480&fit=crop',
        ];

        return [
            'kategori_atk_id' => $kategoriAtkId,
            'nama_atk' => $namaAtk,
            'slug' => Str::slug($namaAtk . '-' . uniqid()),
            'deskripsi' => $deskripsi,
            'harga' => $this->faker->randomFloat(2, 5000, 150000),
            'stok' => $this->faker->numberBetween(10, 500),
            'status_ketersediaan' => $this->faker->randomElement(['Tersedia', 'Tersedia', 'Tersedia', 'Habis']), // Lebih banyak tersedia
            'gambar_utama' => $this->faker->randomElement($atkImages),
        ];
    }

    /**
     * Generate nama ATK yang realistis
     */
    private function generateNamaAtk(): string
    {
        $jenisUtama = $this->faker->randomElement([
            'Pulpen',
            'Pensil',
            'Kertas',
            'Stapler',
            'Gunting',
            'Lem',
            'Penggaris',
            'Penghapus',
            'Spidol',
            'Map',
            'Binder',
            'Kalkulator'
        ]);

        $brand = $this->faker->randomElement([
            'Faber-Castell',
            'Staedtler',
            'Pilot',
            'Bic',
            'Paper Mate',
            'Sharpie',
            '3M',
            'Deli',
            'Joyko',
            'Kenko',
            'Muji',
            'Uni-ball'
        ]);

        $tipe = $this->faker->randomElement([
            "$jenisUtama $brand",
            "$brand $jenisUtama",
            "$jenisUtama Premium",
            "Super $jenisUtama",
            "$jenisUtama Professional",
            "$brand $jenisUtama Gel"
        ]);

        return $tipe;
    }

    /**
     * Generate deskripsi ATK yang panjang dan relevan
     */
    private function generateDeskripsiAtk(): string
    {
        $manfaat = $this->faker->randomElements([
            'meningkatkan produktivitas kerja',
            'memberikan hasil tulisan yang rapi dan jelas',
            'tahan lama dan awet digunakan',
            'ergonomis dan nyaman dipegang',
            'menghemat waktu dan tenaga',
            'memberikan hasil yang profesional',
            'cocok untuk berbagai keperluan kantor',
            'mudah digunakan dan praktis'
        ], 3);

        $fitur = $this->faker->randomElements([
            'tinta yang tidak mudah luntur',
            'ujung pena yang halus dan presisi',
            'grip yang ergonomis dan anti slip',
            'kapasitas tinta yang besar',
            'desain yang modern dan elegan',
            'bahan berkualitas tinggi',
            'warna yang tahan lama',
            'ukuran yang pas untuk genggaman'
        ], 4);

        $aplikasi = $this->faker->randomElements([
            'kantor dan administrasi',
            'sekolah dan pendidikan',
            'rumah dan pribadi',
            'meeting dan presentasi',
            'arsip dan dokumentasi',
            'desain dan kreativitas',
            'catatan dan jurnal',
            'signature dan dokumen resmi'
        ], 3);

        $keunggulan = $this->faker->randomElements([
            'kualitas premium dengan harga terjangkau',
            'garansi kualitas dan ketahanan',
            'ramah lingkungan dan aman digunakan',
            'tersedia dalam berbagai warna dan ukuran',
            'mudah ditemukan dan diganti',
            'kompatibel dengan berbagai jenis kertas',
            'tidak mudah bocor atau tumpah',
            'hasil yang konsisten dan terpercaya'
        ], 3);

        $deskripsi = "Alat tulis berkualitas tinggi yang dirancang khusus untuk " . implode(', ', $aplikasi) . ". ";
        $deskripsi .= "Produk ini dilengkapi dengan " . implode(', ', $fitur) . " yang memberikan pengalaman menulis yang optimal. ";
        $deskripsi .= "Dengan kualitas yang terjamin, alat tulis ini mampu " . implode(', ', $manfaat) . ". ";
        $deskripsi .= "Keunggulan produk meliputi " . implode(', ', $keunggulan) . ". ";

        $petunjukPenggunaan = "Cara penggunaan: pastikan permukaan kertas bersih dan kering, ";
        $petunjukPenggunaan .= "simpan dalam suhu ruangan normal, dan hindari dari sinar matahari langsung. ";

        $deskripsi .= $petunjukPenggunaan;
        $deskripsi .= "Untuk hasil terbaik, gunakan dengan tekanan yang sesuai dan ";
        $deskripsi .= "simpan dalam posisi tegak setelah digunakan. ";
        $deskripsi .= "Produk ini cocok untuk penggunaan sehari-hari dan ";
        $deskripsi .= "memberikan hasil yang profesional untuk berbagai keperluan kantor dan pribadi.";

        return $deskripsi;
    }
}