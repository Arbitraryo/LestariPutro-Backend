<?php
// File: app/Filament/Resources/PesananResource/Pages/CreatePesanan.php

namespace App\Filament\Resources\PesananResource\Pages;

use App\Filament\Resources\PesananResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreatePesanan extends CreateRecord
{
    protected static string $resource = PesananResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan Anda menggunakan 'atk_id' dan 'harga_saat_pesanan' di sini
        $items = $data['items'] ?? [];
        $total = 0;
        if (is_array($items)) {
            foreach ($items as $item) {
                // Perhatikan: 'harga_saat_pesanan' (sesuai model Pesanan)
                $jumlah = $item['jumlah'] ?? 0;
                $harga = $item['harga_saat_pesanan'] ?? 0;
                if (!empty($jumlah) && !empty($harga)) {
                    $total += $jumlah * $harga;
                }
            }
        }
        $data['total_harga'] = $total;
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $itemsData = $data['items'] ?? [];
            $pesananData = Arr::except($data, ['items']);

            if (!isset($pesananData['total_harga'])) {
                $total = 0;
                if (is_array($itemsData)) {
                    foreach ($itemsData as $item) {
                        $jumlah = $item['jumlah'] ?? 0;
                        $harga = $item['harga_saat_pesanan'] ?? 0;
                        if (!empty($jumlah) && !empty($harga)) {
                            $total += $jumlah * $harga;
                        }
                    }
                }
                $pesananData['total_harga'] = $total;
            }

            Log::info('Membuat record Pesanan utama...', $pesananData);
            $record = static::getModel()::create($pesananData);
            Log::info("Record Pesanan utama dibuat, ID: {$record->id}");

            Log::info('Memulai proses attach item...');
            if (is_array($itemsData)) {
                foreach ($itemsData as $item) {
                    // GUNAKAN 'atk_id'
                    $atkId = $item['atk_id'] ?? null;
                    $jumlah = $item['jumlah'] ?? 0;
                    $harga = $item['harga_saat_pesanan'] ?? 0;

                    if ($atkId && $jumlah > 0) {
                        Log::info("Attaching ATK ID: {$atkId} with Jumlah: {$jumlah}, Harga: {$harga}");
                        try {
                            $record->items()->attach($atkId, [
                                'jumlah' => $jumlah,
                                'harga_saat_pesanan' => $harga
                            ]);
                            Log::info("Berhasil attach ATK ID: {$atkId}");
                        } catch (\Exception $e) {
                            Log::error("Gagal attach ATK ID: {$atkId} - " . $e->getMessage());
                            // throw $e;
                        }
                    } else {
                        Log::warning('Skipping item, ATK ID atau Jumlah tidak valid:', $item);
                    }
                }
            }
            Log::info("Proses attach item selesai untuk Pesanan ID: {$record->id}");

            return $record;
        });
    }
}