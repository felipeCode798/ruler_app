<?php

namespace App\Filament\Resources\FilterConfigurationResource\Pages;

use App\Filament\Resources\FilterConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFilterConfiguration extends CreateRecord
{
    protected static string $resource = FilterConfigurationResource::class;
    protected static ?string $title = 'Crear Configuración de Filtro';
}
