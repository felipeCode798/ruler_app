<?php

namespace App\Filament\Personal\Resources\PinsResource\Pages;

use App\Filament\Personal\Resources\PinsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPins extends EditRecord
{
    protected static string $resource = PinsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
