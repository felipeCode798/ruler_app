<?php

namespace App\Filament\Resources\FilterResource\Pages;

use App\Filament\Resources\FilterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFilters extends ListRecords
{
    protected static string $resource = FilterResource::class;
    protected static ?string $title = 'Filtros';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Crear Filtro'),
        ];
    }
}
