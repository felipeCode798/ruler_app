<?php

namespace App\Filament\Resources\RegistrarProcesoResource\Pages;

use App\Filament\Resources\RegistrarProcesoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRegistrarProceso extends CreateRecord
{
    protected static string $resource = RegistrarProcesoResource::class;
    protected static ?string $title = 'Registrar Proceso';
}
