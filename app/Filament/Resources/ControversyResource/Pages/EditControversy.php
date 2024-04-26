<?php

namespace App\Filament\Resources\ControversyResource\Pages;

use App\Filament\Resources\ControversyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditControversy extends EditRecord
{
    protected static string $resource = ControversyResource::class;
    protected static ?string $title = 'Editar Controversia';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
