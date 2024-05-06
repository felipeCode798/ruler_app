<?php

namespace App\Filament\Resources\SchoolSetupResource\Pages;

use App\Filament\Resources\SchoolSetupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolSetup extends EditRecord
{
    protected static string $resource = SchoolSetupResource::class;
    protected static ?string $title = 'Editar Configuración de la Escuela';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
