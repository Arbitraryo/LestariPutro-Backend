<?php

namespace App\Filament\Resources\AtkResource\Pages;

use App\Filament\Resources\AtkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAtk extends EditRecord
{
    protected static string $resource = AtkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}