<?php

namespace App\Filament\Resources\PinResource\Pages;

use App\Filament\Resources\PinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPin extends EditRecord
{
    protected static string $resource = PinResource::class;
    protected static ?string $title = 'Editar pin';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
