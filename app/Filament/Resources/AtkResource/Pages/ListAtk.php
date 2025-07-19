<?php

namespace App\Filament\Resources\AtkResource\Pages;

use App\Filament\Resources\AtkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAtk extends ListRecords
{
    protected static string $resource = AtkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}