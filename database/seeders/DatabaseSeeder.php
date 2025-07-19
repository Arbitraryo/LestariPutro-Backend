<?php

namespace Database\Seeders;

// Gunakan use untuk mengimpor semua model yang dibutuhkan
use App\Models\KategoriAtk;
use App\Models\Pesanan;
use App\Models\Atk;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. BUAT ROLES
        // ===========================================
        $this->command->info('Membuat Roles...');
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin']
        );
        // DIUBAH: Membuat role 'User' dengan slug 'user'
        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'User']
        );
        $this->command->info('Roles dibuat.');

        // 2. BUAT USER
        // ===========================================
        $this->command->info('Membuat Users...');
        // User Admin
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@atk.com'],
            [
                'name' => 'Admin ATK',
                'password' => Hash::make('password'),
            ]
        );
        $adminUser->roles()->sync($adminRole->id); // Attach role Admin ke user Admin
        $this->command->info('User Admin dibuat dan role di-assign.');

        // User Pelanggan (sekarang dengan role 'user')
        $this->command->info('Membuat Pelanggan dan meng-assign role "User"...');
        $pelangganUsers = User::factory(10)->create();
        foreach ($pelangganUsers as $pelanggan) {
            $pelanggan->roles()->sync($userRole->id); // Attach role User ke user Pelanggan
        }
        $this->command->info('Pelanggan dibuat dan role "User" di-assign.');


        // 3. BUAT DATA PRODUK
        // ===========================================
        $this->command->info('Membuat Kategori dan ATK...');
        KategoriAtk::factory(5)->create();
        $atkList = Atk::factory(30)->create();
        $this->command->info('Kategori dan ATK dibuat.');


        // 4. BUAT DATA PESANAN (LOGIKA PALING KOMPLEKS)
        // ===========================================
        $this->command->info('Membuat Pesanan dan item-itemnya...');
        // Pastikan pesanan dibuat oleh user dengan role pelanggan (user)
        Pesanan::factory(25)->create([
            'user_id' => $pelangganUsers->random()->id,
        ])->each(function (Pesanan $pesanan) use ($atkList) {
            // Untuk setiap pesanan, tambahkan 1 sampai 3 jenis ATK secara acak
            $itemsToAttach = $atkList->random(rand(1, 3));
            $totalHarga = 0;

            foreach ($itemsToAttach as $atk) {
                $jumlah = rand(1, 5);
                $hargaSaatPesan = $atk->harga;
                $totalHarga += $jumlah * $hargaSaatPesan;

                // Membuat item pesanan baru (bukan attach, karena relasi HasMany)
                $pesanan->items()->create([
                    'atk_id' => $atk->id,
                    'jumlah' => $jumlah,
                    'harga_saat_pesanan' => $hargaSaatPesan,
                ]);
            }

            // Setelah semua item ditambahkan, update total harga di pesanan utama
            $pesanan->update(['total_harga' => $totalHarga]);
        });
        $this->command->info('Pesanan dan item-itemnya dibuat.');

        $this->command->info('Database seeding selesai!');
    }
}