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
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_pelanggan', 120);
            $table->string('nomor_whatsapp', 30);
            $table->text('alamat_pengiriman');
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->string('metode_pembayaran', 50)->nullable();
            $table->string('status_pembayaran', 50)->nullable();
            $table->date('tanggal_pesanan');
            $table->string('status', 50)->default('Baru');
            $table->string('nomor_resi', 100)->nullable();
            $table->string('payment_proof_path', 255)->nullable();
            $table->text('catatan')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};