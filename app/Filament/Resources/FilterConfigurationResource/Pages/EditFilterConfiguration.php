<?php

namespace App\Filament\Resources\FilterConfigurationResource\Pages;

use App\Filament\Resources\FilterConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFilterConfiguration extends EditRecord
{
    protected static string $resource = FilterConfigurationResource::class;
    protected static ?string $title = 'Editar Configuración de Filtro';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
