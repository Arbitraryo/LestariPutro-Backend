<?php
// File: app/Filament/Resources/PesananResource/Pages/EditPesanan.php

namespace App\Filament\Resources\PesananResource\Pages;

use App\Filament\Resources\PesananResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class EditPesanan extends EditRecord
{
    protected static string $resource = PesananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var \App\Models\Pesanan $pesananRecord */
        $pesananRecord = $this->getRecord();
        $pesananRecord->loadMissing('items');

        $itemsDataFormatted = [];
        if ($pesananRecord->relationLoaded('items') && $pesananRecord->items->isNotEmpty()) {
            foreach ($pesananRecord->items as $atkDalamPesanan) {
                $pivotData = $atkDalamPesanan->pivot;
                if ($pivotData) {
                    $itemsDataFormatted[] = [
                        'atk_id' => $atkDalamPesanan->id,
                        'jumlah' => $pivotData->jumlah,
                        'harga_saat_pesanan' => $pivotData->harga_saat_pesanan,
                    ];
                }
            }
        }
        $data['items'] = $itemsDataFormatted;

        $total = 0;
        foreach ($itemsDataFormatted as $item) {
            $jumlah = $item['jumlah'] ?? 0;
            $harga = $item['harga_saat_pesanan'] ?? 0;
            if (is_numeric($jumlah) && is_numeric($harga)) {
                $total += $jumlah * $harga;
            }
        }
        $data['total_harga'] = $total;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            /** @var \App\Models\Pesanan $record */
            $itemsDataFromForm = $data['items'] ?? [];
            $pesananDataToUpdate = Arr::except($data, ['items']);

            $calculatedTotal = 0;
            $pivotDataForSync = [];
            if (is_array($itemsDataFromForm)) {
                foreach ($itemsDataFromForm as $item) {
                    // GUNAKAN 'atk_id'
                    $atkId = $item['atk_id'] ?? null;
                    $jumlah = $item['jumlah'] ?? 0;
                    $harga = $item['harga_saat_pesanan'] ?? 0;

                    if ($atkId && is_numeric($jumlah) && $jumlah > 0 && is_numeric($harga)) {
                        $calculatedTotal += $jumlah * $harga;
                        $pivotDataForSync[$atkId] = [
                            'jumlah' => $jumlah,
                            'harga_saat_pesanan' => $harga,
                        ];
                    }
                }
            }
            $pesananDataToUpdate['total_harga'] = $calculatedTotal;

            $record->fill($pesananDataToUpdate);
            $record->save();

            // Jangan gunakan sync() pada HasMany
            // $pesanan->items()->sync($data);

            // Jika ingin update semua item:
            $record->items()->delete();
            $record->items()->createMany($data['items'] ?? []);

            $record->refresh()->load('items');

            Notification::make()
                ->title('Pesanan berhasil diperbarui')
                ->success()
                ->send();

            return $record;
        });
    }
}
