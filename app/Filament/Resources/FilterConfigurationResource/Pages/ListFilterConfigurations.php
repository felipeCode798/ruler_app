<?php

namespace App\Filament\Resources\FilterConfigurationResource\Pages;

use App\Filament\Resources\FilterConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFilterConfigurations extends ListRecords
{
    protected static string $resource = FilterConfigurationResource::class;
    protected static ?string $title = 'Configuraciones de Filtros';


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Crear Configuración de Filtro'),
        ];
    }
}
