<?php

namespace App\Filament\Resources\RegistrarProcesoResource\Pages;

use App\Filament\Resources\RegistrarProcesoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegistrarProceso extends EditRecord
{
    protected static string $resource = RegistrarProcesoResource::class;
    protected static ?string $title = 'Editar Proceso';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
