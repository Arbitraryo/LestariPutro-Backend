<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('atk', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel kategori_atk
            $table->foreignId('kategori_atk_id')->constrained('kategori_atk')->cascadeOnDelete();
            $table->string('nama_atk', 150);
            $table->string('slug', 170)->unique();
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 15, 2); // Menggunakan 2 angka di belakang koma untuk Rupiah
            $table->integer('stok')->default(0);
            $table->string('status_ketersediaan', 50)->default('Tersedia');
            $table->string('gambar_utama', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atk');
    }
};