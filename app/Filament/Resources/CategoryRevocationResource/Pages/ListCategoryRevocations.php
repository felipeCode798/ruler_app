<?php

namespace App\Filament\Resources\CategoryRevocationResource\Pages;

use App\Filament\Resources\CategoryRevocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoryRevocations extends ListRecords
{
    protected static string $resource = CategoryRevocationResource::class;
    protected static ?string $title = 'Categorias';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Crear Categoria'),
        ];
    }
}
