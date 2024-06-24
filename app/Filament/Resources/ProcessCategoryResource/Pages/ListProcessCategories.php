<?php

namespace App\Filament\Resources\ProcessCategoryResource\Pages;

use App\Filament\Resources\ProcessCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProcessCategories extends ListRecords
{
    protected static string $resource = ProcessCategoryResource::class;
    protected static ?string $title = 'Proceso';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Crear Configuracion De Proceso'),
        ];
    }
}
