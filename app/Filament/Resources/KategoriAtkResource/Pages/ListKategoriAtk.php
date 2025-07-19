<?php

namespace App\Filament\Resources\KategoriAtkResource\Pages;

use App\Filament\Resources\KategoriAtkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKategoriAtk extends ListRecords
{
    protected static string $resource = KategoriAtkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}