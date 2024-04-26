<?php

namespace App\Filament\Resources\CoerciveCollectionResource\Pages;

use App\Filament\Resources\CoerciveCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoerciveCollections extends ListRecords
{
    protected static string $resource = CoerciveCollectionResource::class;
    protected static ?string $title = 'Cobros Coactivos';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Crear Cobro Coactivo'),
        ];
    }
}
