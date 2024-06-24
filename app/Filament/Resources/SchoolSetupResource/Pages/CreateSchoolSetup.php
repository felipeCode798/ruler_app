<?php

namespace App\Filament\Resources\SchoolSetupResource\Pages;

use App\Filament\Resources\SchoolSetupResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSchoolSetup extends CreateRecord
{
    protected static string $resource = SchoolSetupResource::class;
    protected static ?string $title = 'Crear Configuracion De Escuela';
}
